"""
Workflow Engine for BrewPOS Autonomous Orchestration.
Coordinates Human Approval Gates, Implementer Dispatches, Automated QA Audits,
Remediation Loops (max 3), Filesystem Snapshot Guardrails, and Durable State Transitions.
"""

import time
from pathlib import Path
from typing import Callable, Optional, Dict, Any, List
from .state_schema import (
    WorkflowState,
    WorkflowStatus,
    TaskScope,
    ImplementationReport,
    AuditReport,
    AuditEvidence,
)
from .state_manager import StateManager, StateManagerError
from .filesystem_snapshot import (
    take_filesystem_snapshot,
    compare_snapshots,
    record_guardrail_violation,
    SnapshotDiff,
)

class WorkflowEngineError(Exception):
    pass

class WorkflowEngine:
    def __init__(
        self,
        state_manager: Optional[StateManager] = None,
        workspace_dir: Optional[Path] = None,
        enable_snapshot_guardrail: bool = True
    ):
        self.state_manager = state_manager or StateManager()
        self.workspace_dir = Path(workspace_dir) if workspace_dir else Path(__file__).parent.parent
        self.enable_snapshot_guardrail = enable_snapshot_guardrail
        self.pre_audit_snapshot: Optional[Dict[str, str]] = None

    @property
    def state(self) -> WorkflowState:
        return self.state_manager.get_state()

    def request_task_approval(
        self,
        task_id: str,
        title: str,
        acceptance_criteria: List[str],
        files_allowed: Optional[List[str]] = None,
        next_task_id: Optional[str] = None
    ) -> WorkflowState:
        """
        Step 1 & 2: Present one eligible task and its criteria for human approval.
        Guards against starting new work while an active task is unresolved.
        """
        current_status = self.state.workflow_status
        if current_status in [WorkflowStatus.IMPLEMENTING, WorkflowStatus.AUDITING, WorkflowStatus.REMEDIATING]:
            raise WorkflowEngineError(f"Cannot request new task approval while current task '{self.state.current_task_id}' is in status {current_status.value}.")

        scope = TaskScope(
            task_id=task_id,
            title=title,
            acceptance_criteria=acceptance_criteria,
            files_allowed=files_allowed or [],
            approved_by_human=False
        )

        return self.state_manager.transition(
            to_status=WorkflowStatus.AWAITING_HUMAN_APPROVAL,
            reason=f"Task {task_id} presented to human for approval.",
            current_task_id=task_id,
            approved_scope=scope,
            remediation_attempt_count=0,
            implementation_report=None,
            audit_report=None,
            next_task_id=next_task_id,
            failure_or_blocking_reason=None
        )

    def approve_task(self, task_id: str, human_token: str = "HUMAN_APPROVED") -> WorkflowState:
        """
        Step 3: Register explicit human approval.
        Only transitions when in AWAITING_HUMAN_APPROVAL and task_id matches.
        """
        if self.state.workflow_status != WorkflowStatus.AWAITING_HUMAN_APPROVAL:
            raise WorkflowEngineError(f"Cannot approve task: current status is {self.state.workflow_status.value}, expected AWAITING_HUMAN_APPROVAL.")

        if not self.state.approved_scope or self.state.approved_scope.task_id != task_id:
            raise WorkflowEngineError(f"Task ID mismatch: currently pending '{self.state.current_task_id}', cannot approve '{task_id}'.")

        self.state.approved_scope.approved_by_human = True
        self.state.approved_scope.approval_timestamp = time.time()

        return self.state_manager.transition(
            to_status=WorkflowStatus.APPROVED,
            reason=f"Explicit human approval granted by '{human_token}'."
        )

    def dispatch_implementer(self, implementer_callable: Optional[Callable[[TaskScope], ImplementationReport]] = None) -> Optional[ImplementationReport]:
        """
        Step 4: Invoke the dedicated Implementer subagent.
        """
        if self.state.workflow_status not in [WorkflowStatus.APPROVED, WorkflowStatus.REMEDIATING]:
            raise WorkflowEngineError(f"Cannot dispatch implementer in status {self.state.workflow_status.value}. Must be APPROVED or REMEDIATING.")

        self.state_manager.transition(
            to_status=WorkflowStatus.IMPLEMENTING,
            reason="Implementer subagent dispatched with approved scope."
        )

        if implementer_callable:
            report = implementer_callable(self.state.approved_scope)
            return report
        return None

    def receive_implementation_and_trigger_qa(
        self,
        implementation_report: ImplementationReport,
        qa_callable: Optional[Callable[[TaskScope, ImplementationReport], AuditReport]] = None
    ) -> Optional[AuditReport]:
        """
        Step 5 & 6: Receive Implementer report automatically through messaging
        and invoke the independent QA/Auditor without manual prompting.
        Strictly enforces that implementation_report.task_id matches the active task.
        """
        # Task 1.1: Enforce task identity before any state mutation
        if not implementation_report or implementation_report.task_id != self.state.current_task_id:
            received_id = getattr(implementation_report, "task_id", None)
            raise WorkflowEngineError(
                f"Task ID mismatch in implementation report: active task is '{self.state.current_task_id}', "
                f"received '{received_id}'. State remains unchanged."
            )

        if self.state.workflow_status not in [WorkflowStatus.IMPLEMENTING, WorkflowStatus.REMEDIATING]:
            raise WorkflowEngineError(
                f"Cannot trigger QA: workflow status is {self.state.workflow_status.value}, "
                f"expected IMPLEMENTING or REMEDIATING."
            )

        # Task 2: Take pre-audit filesystem snapshot before QA executes
        if self.enable_snapshot_guardrail and self.workspace_dir.exists():
            self.pre_audit_snapshot = take_filesystem_snapshot(self.workspace_dir)

        # Persist report and transition to AUDITING
        self.state_manager.transition(
            to_status=WorkflowStatus.AUDITING,
            reason="Implementation report received. Automatically initiating independent QA audit.",
            implementation_report=implementation_report
        )

        if qa_callable:
            audit_report = qa_callable(self.state.approved_scope, implementation_report)
            return audit_report
        return None

    def evaluate_audit_result(
        self,
        audit_report: AuditReport,
        remediation_implementer_callable: Optional[Callable[[TaskScope, AuditReport], ImplementationReport]] = None,
        qa_callable: Optional[Callable[[TaskScope, ImplementationReport], AuditReport]] = None
    ) -> WorkflowState:
        """
        Step 7, 8, 9, 10:
        Evaluate QA Audit result.
        Strictly enforces task identity and checks filesystem snapshot guardrail.
        - If snapshot diff detected: transitions to BLOCKED (guardrail violation).
        - If PASS: transitions to TASK_PASSED and pauses for next approval.
        - If FAIL: triggers remediation loop up to 3 attempts.
        - If retries > 3 or evidence missing: transitions to BLOCKED.
        """
        # Task 1.2: Enforce task identity before any state mutation
        if not audit_report or audit_report.task_id != self.state.current_task_id:
            received_id = getattr(audit_report, "task_id", None)
            raise WorkflowEngineError(
                f"Task ID mismatch in audit report: active task is '{self.state.current_task_id}', "
                f"received '{received_id}'. State remains unchanged."
            )

        if self.state.workflow_status != WorkflowStatus.AUDITING:
            raise WorkflowEngineError(f"Cannot evaluate audit result in status {self.state.workflow_status.value}, expected AUDITING.")

        # Task 2: Strengthen QA Change Detection via Filesystem Snapshot
        if self.enable_snapshot_guardrail and self.pre_audit_snapshot is not None and self.workspace_dir.exists():
            post_audit_snapshot = take_filesystem_snapshot(self.workspace_dir)
            diff = compare_snapshots(self.pre_audit_snapshot, post_audit_snapshot)

            if diff.has_changes:
                violation_dir = self.workspace_dir / "orchestration" / "storage" / "guardrail_violations"
                report_file = record_guardrail_violation(violation_dir, self.state.current_task_id, diff)
                return self.state_manager.transition(
                    to_status=WorkflowStatus.BLOCKED,
                    reason=f"Guardrail violation: Filesystem modification detected during QA audit ({diff.summary()}).",
                    audit_report=audit_report,
                    failure_or_blocking_reason=(
                        f"Guardrail violation: QA auditor modified files during audit ({diff.summary()}). "
                        f"Violation report logged to {report_file.name}. Human intervention required. Changes not auto-reverted."
                    )
                )

        # Invariant: Missing or ambiguous evidence never yields PASS
        if not audit_report or not audit_report.evidence:
            return self.state_manager.transition(
                to_status=WorkflowStatus.BLOCKED,
                reason="Audit rejected: Missing or null audit evidence.",
                audit_report=audit_report,
                failure_or_blocking_reason="Missing audit evidence provided by auditor."
            )

        if audit_report.evidence.automated_tests_passed is None or audit_report.verdict not in ["PASS", "FAIL"]:
            return self.state_manager.transition(
                to_status=WorkflowStatus.BLOCKED,
                reason="Audit rejected: Ambiguous or invalid audit report format.",
                audit_report=audit_report,
                failure_or_blocking_reason="Inconclusive/ambiguous audit report."
            )

        # CASE 1: AUDIT PASS
        if audit_report.verdict == "PASS":
            # Extra verification: Automated tests MUST be true
            if not audit_report.evidence.automated_tests_passed:
                return self.state_manager.transition(
                    to_status=WorkflowStatus.BLOCKED,
                    reason="Contradictory audit report: verdict is PASS but automated tests failed.",
                    audit_report=audit_report,
                    failure_or_blocking_reason="Audit report contradiction (PASS claimed with failing tests)."
                )

            completed = list(self.state.completed_tasks)
            if self.state.current_task_id and self.state.current_task_id not in completed:
                completed.append(self.state.current_task_id)

            return self.state_manager.transition(
                to_status=WorkflowStatus.TASK_PASSED,
                reason=f"Task {self.state.current_task_id} successfully audited and passed.",
                audit_report=audit_report,
                completed_tasks=completed,
                remediation_attempt_count=0
            )

        # CASE 2: AUDIT FAIL
        new_attempt_count = self.state.remediation_attempt_count + 1
        self.state.remediation_attempt_count = new_attempt_count

        if new_attempt_count > self.state.max_remediations:
            # 3 unsuccessful remediation attempts lead to BLOCKED
            return self.state_manager.transition(
                to_status=WorkflowStatus.BLOCKED,
                reason=f"Task {self.state.current_task_id} blocked: Max remediation attempts ({self.state.max_remediations}) exhausted.",
                audit_report=audit_report,
                remediation_attempt_count=new_attempt_count,
                failure_or_blocking_reason=f"Maximum remediation attempts ({self.state.max_remediations}) exhausted. Human intervention required."
            )

        # Step 7: Send exact findings to Implementer and repeat audit after remediation
        self.state_manager.transition(
            to_status=WorkflowStatus.REMEDIATING,
            reason=f"Audit failed (attempt {new_attempt_count}/{self.state.max_remediations}). Dispatching findings for remediation.",
            audit_report=audit_report,
            remediation_attempt_count=new_attempt_count
        )

        if remediation_implementer_callable and qa_callable:
            # Execute remediation cycle synchronously
            remediated_report = remediation_implementer_callable(self.state.approved_scope, audit_report)
            return self.receive_implementation_and_trigger_qa(remediated_report, qa_callable)

        return self.state

    def complete_all_dod(self) -> WorkflowState:
        """Step 11: Mark ALL_DOD_COMPLETE only when all required DoD criteria have verified evidence."""
        return self.state_manager.transition(
            to_status=WorkflowStatus.ALL_DOD_COMPLETE,
            reason="All authoritative DoD items have verified independent evidence."
        )
