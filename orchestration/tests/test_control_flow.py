"""
Automated Control-Flow Test Suite for BrewPOS Orchestrator.
Demonstrates and strictly verifies all 7 core workflow requirements:
1. An approved task triggers Implementer.
2. Implementer completion triggers QA without manual prompting.
3. FAIL triggers remediation and a subsequent audit.
4. PASS stops the loop and requests approval for the next task.
5. Three unsuccessful remediation attempts lead to BLOCKED.
6. Missing or ambiguous evidence never yields PASS.
7. Restarting the orchestrator does not silently lose task state.
"""

import unittest
import tempfile
import os
import shutil
from pathlib import Path

from orchestration.state_schema import (
    WorkflowStatus,
    TaskScope,
    ImplementationReport,
    AuditReport,
    AuditEvidence,
)
from orchestration.state_manager import StateManager, StateManagerError
from orchestration.workflow_engine import WorkflowEngine, WorkflowEngineError

class TestOrchestratorControlFlow(unittest.TestCase):
    def setUp(self):
        self.test_dir = tempfile.mkdtemp()
        self.state_file = Path(self.test_dir) / "test_state.json"
        self.state_manager = StateManager(self.state_file)
        self.engine = WorkflowEngine(self.state_manager)

    def tearDown(self):
        shutil.rmtree(self.test_dir, ignore_errors=True)

    def test_01_approved_task_triggers_implementer(self):
        """Verify: An approved task transitions to IMPLEMENTING and invokes the Implementer."""
        # 1. Present task for approval
        self.engine.request_task_approval(
            task_id="POS-05.1",
            title="Barcode DB Migration",
            acceptance_criteria=["Add barcode column to products table"]
        )
        self.assertEqual(self.engine.state.workflow_status, WorkflowStatus.AWAITING_HUMAN_APPROVAL)

        # Ensure Implementer cannot be dispatched before approval
        with self.assertRaises(WorkflowEngineError):
            self.engine.dispatch_implementer()

        # 2. Grant approval
        self.engine.approve_task("POS-05.1", human_token="USER_TOKEN_OK")
        self.assertEqual(self.engine.state.workflow_status, WorkflowStatus.APPROVED)
        self.assertTrue(self.engine.state.approved_scope.approved_by_human)

        # 3. Dispatch Implementer
        mock_invoked = []
        def mock_implementer(scope):
            mock_invoked.append(scope.task_id)
            return ImplementationReport(task_id=scope.task_id, status="SUCCESS", summary="Migration created")

        report = self.engine.dispatch_implementer(mock_implementer)
        self.assertEqual(self.engine.state.workflow_status, WorkflowStatus.IMPLEMENTING)
        self.assertEqual(mock_invoked, ["POS-05.1"])
        self.assertEqual(report.status, "SUCCESS")

    def test_02_implementer_completion_triggers_qa_without_manual_prompting(self):
        """Verify: Implementer completion report automatically triggers QA without manual prompting."""
        self.engine.request_task_approval("POS-05.1", "Barcode Migration", ["Criteria"])
        self.engine.approve_task("POS-05.1")
        self.engine.dispatch_implementer()

        imp_report = ImplementationReport(
            task_id="POS-05.1",
            status="SUCCESS",
            summary="Added column",
            files_changed=["database/migrations/2026_10_15_000002_add_barcode.php"]
        )

        qa_invoked = []
        def mock_qa(scope, report):
            qa_invoked.append((scope.task_id, report.summary))
            return AuditReport(
                task_id=scope.task_id,
                verdict="PASS",
                evidence=AuditEvidence(
                    automated_tests_passed=True,
                    test_output="25 passed",
                    security_audit_passed=True,
                    browser_verification_passed=True
                )
            )

        # This call receives implementer output and triggers QA automatically
        audit_report = self.engine.receive_implementation_and_trigger_qa(imp_report, mock_qa)
        self.assertEqual(self.engine.state.workflow_status, WorkflowStatus.AUDITING)
        self.assertEqual(len(qa_invoked), 1)
        self.assertEqual(qa_invoked[0][0], "POS-05.1")
        self.assertEqual(audit_report.verdict, "PASS")

    def test_03_fail_triggers_remediation_and_subsequent_audit(self):
        """Verify: Audit FAIL triggers remediation loop with exact findings and subsequent audit."""
        self.engine.request_task_approval("POS-05.1", "Barcode Migration", ["Criteria"])
        self.engine.approve_task("POS-05.1")
        self.engine.dispatch_implementer()

        imp_report = ImplementationReport(task_id="POS-05.1", status="SUCCESS", summary="Initial attempt")
        self.engine.receive_implementation_and_trigger_qa(imp_report)

        # First audit fails
        fail_audit = AuditReport(
            task_id="POS-05.1",
            verdict="FAIL",
            evidence=AuditEvidence(
                automated_tests_passed=False,
                test_output="Syntax error in migration",
                security_audit_passed=False,
                browser_verification_passed=False
            ),
            defects=[{"severity": "HIGH", "file": "migration.php", "reason": "Missing semicolon"}]
        )

        remediation_called = []
        def mock_remediation(scope, audit):
            remediation_called.append(audit.defects[0]["reason"])
            return ImplementationReport(task_id=scope.task_id, status="SUCCESS", summary="Fixed semicolon")

        re_audit_called = []
        def mock_re_audit(scope, rep):
            re_audit_called.append(rep.summary)
            return AuditReport(
                task_id=scope.task_id,
                verdict="PASS",
                evidence=AuditEvidence(
                    automated_tests_passed=True,
                    test_output="All tests pass",
                    security_audit_passed=True,
                    browser_verification_passed=True
                )
            )

        # Trigger remediation cycle
        self.engine.evaluate_audit_result(
            fail_audit,
            remediation_implementer_callable=mock_remediation,
            qa_callable=mock_re_audit
        )

        # Assert remediation was dispatched with exact findings
        self.assertEqual(remediation_called, ["Missing semicolon"])
        self.assertEqual(re_audit_called, ["Fixed semicolon"])
        self.assertEqual(self.engine.state.remediation_attempt_count, 1)
        self.assertEqual(self.engine.state.workflow_status, WorkflowStatus.AUDITING)

    def test_04_pass_stops_loop_and_requests_approval_for_next_task(self):
        """Verify: PASS stops the loop, records completion, and resets for next approval."""
        self.engine.request_task_approval("POS-05.1", "Barcode Migration", ["Criteria"], next_task_id="POS-05.2")
        self.engine.approve_task("POS-05.1")
        self.engine.dispatch_implementer()

        imp_report = ImplementationReport(task_id="POS-05.1", status="SUCCESS", summary="Done")
        self.engine.receive_implementation_and_trigger_qa(imp_report)

        pass_audit = AuditReport(
            task_id="POS-05.1",
            verdict="PASS",
            evidence=AuditEvidence(
                automated_tests_passed=True,
                test_output="26 tests pass",
                security_audit_passed=True,
                browser_verification_passed=True
            )
        )

        state = self.engine.evaluate_audit_result(pass_audit)
        self.assertEqual(state.workflow_status, WorkflowStatus.TASK_PASSED)
        self.assertIn("POS-05.1", state.completed_tasks)
        self.assertEqual(state.remediation_attempt_count, 0)

        # Next step: must require explicit human approval for next task
        self.engine.request_task_approval("POS-05.2", "Model Fillable", ["Add barcode to fillable"])
        self.assertEqual(self.engine.state.workflow_status, WorkflowStatus.AWAITING_HUMAN_APPROVAL)
        self.assertEqual(self.engine.state.current_task_id, "POS-05.2")

    def test_05_three_unsuccessful_remediation_attempts_lead_to_blocked(self):
        """Verify: Exactly three unsuccessful remediation attempts transition the workflow to BLOCKED."""
        self.engine.request_task_approval("POS-05.1", "Barcode Migration", ["Criteria"])
        self.engine.approve_task("POS-05.1")
        self.engine.dispatch_implementer()

        imp_report = ImplementationReport(task_id="POS-05.1", status="SUCCESS", summary="Initial")
        self.engine.receive_implementation_and_trigger_qa(imp_report)

        failing_evidence = AuditEvidence(
            automated_tests_passed=False,
            test_output="Failed",
            security_audit_passed=False,
            browser_verification_passed=False
        )

        def make_fail_report():
            return AuditReport(task_id="POS-05.1", verdict="FAIL", evidence=failing_evidence)

        # Attempt 1
        self.engine.evaluate_audit_result(make_fail_report())
        self.assertEqual(self.engine.state.workflow_status, WorkflowStatus.REMEDIATING)
        self.assertEqual(self.engine.state.remediation_attempt_count, 1)

        # Transition back to auditing for attempt 2
        self.state_manager.transition(WorkflowStatus.AUDITING, "Re-audit 2")
        self.engine.evaluate_audit_result(make_fail_report())
        self.assertEqual(self.engine.state.workflow_status, WorkflowStatus.REMEDIATING)
        self.assertEqual(self.engine.state.remediation_attempt_count, 2)

        # Transition back to auditing for attempt 3
        self.state_manager.transition(WorkflowStatus.AUDITING, "Re-audit 3")
        self.engine.evaluate_audit_result(make_fail_report())
        self.assertEqual(self.engine.state.workflow_status, WorkflowStatus.REMEDIATING)
        self.assertEqual(self.engine.state.remediation_attempt_count, 3)

        # Transition back to auditing for attempt 4 (exceeding 3)
        self.state_manager.transition(WorkflowStatus.AUDITING, "Re-audit 4")
        self.engine.evaluate_audit_result(make_fail_report())
        self.assertEqual(self.engine.state.workflow_status, WorkflowStatus.BLOCKED)
        self.assertEqual(self.engine.state.remediation_attempt_count, 4)
        self.assertIn("exhausted", self.engine.state.failure_or_blocking_reason)

    def test_06_missing_or_ambiguous_evidence_never_yields_pass(self):
        """Verify: Missing or contradictory evidence never yields PASS, transitions to BLOCKED."""
        self.engine.request_task_approval("POS-05.1", "Barcode Migration", ["Criteria"])
        self.engine.approve_task("POS-05.1")
        self.engine.dispatch_implementer()
        self.engine.receive_implementation_and_trigger_qa(ImplementationReport("POS-05.1", "SUCCESS", "Done"))

        # Case A: None evidence
        bad_audit_1 = AuditReport(task_id="POS-05.1", verdict="PASS", evidence=None)
        state1 = self.engine.evaluate_audit_result(bad_audit_1)
        self.assertEqual(state1.workflow_status, WorkflowStatus.BLOCKED)

        # Case B: Contradictory evidence (verdict PASS but tests failed)
        self.state_manager.transition(WorkflowStatus.AUDITING, "Reset to auditing")
        bad_audit_2 = AuditReport(
            task_id="POS-05.1",
            verdict="PASS",
            evidence=AuditEvidence(
                automated_tests_passed=False,  # Contradiction!
                test_output="Fatal Error",
                security_audit_passed=True,
                browser_verification_passed=True
            )
        )
        state2 = self.engine.evaluate_audit_result(bad_audit_2)
        self.assertEqual(state2.workflow_status, WorkflowStatus.BLOCKED)
        self.assertIn("contradiction", state2.failure_or_blocking_reason.lower())

    def test_07_restarting_orchestrator_does_not_lose_task_state(self):
        """Verify: Terminating and re-instantiating the orchestrator restores durable state intact."""
        self.engine.request_task_approval("POS-05.1", "Barcode Migration", ["Criteria"])
        self.engine.approve_task("POS-05.1")
        self.engine.dispatch_implementer()

        imp_report = ImplementationReport(
            task_id="POS-05.1",
            status="SUCCESS",
            summary="First migration written",
            files_changed=["path/file.php"]
        )
        self.engine.receive_implementation_and_trigger_qa(imp_report)

        # Simulate process termination / restart
        del self.engine
        del self.state_manager

        # Re-initialize new instances pointing to same file
        recovered_manager = StateManager(self.state_file)
        recovered_engine = WorkflowEngine(recovered_manager)

        recovered_state = recovered_engine.state
        self.assertEqual(recovered_state.current_task_id, "POS-05.1")
        self.assertEqual(recovered_state.workflow_status, WorkflowStatus.AUDITING)
        self.assertEqual(recovered_state.implementation_report.summary, "First migration written")
        self.assertTrue(recovered_state.approved_scope.approved_by_human)
        self.assertGreater(len(recovered_state.history), 0)

if __name__ == "__main__":
    unittest.main()
