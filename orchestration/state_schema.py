"""
State schema definitions for BrewPOS Autonomous Orchestration.
"""

from dataclasses import dataclass, field, asdict
from typing import Dict, List, Optional, Any
from enum import Enum
import time

class WorkflowStatus(str, Enum):
    IDLE = "IDLE"
    AWAITING_HUMAN_APPROVAL = "AWAITING_HUMAN_APPROVAL"
    APPROVED = "APPROVED"
    IMPLEMENTING = "IMPLEMENTING"
    AUDITING = "AUDITING"
    REMEDIATING = "REMEDIATING"
    TASK_PASSED = "TASK_PASSED"
    BLOCKED = "BLOCKED"
    ALL_DOD_COMPLETE = "ALL_DOD_COMPLETE"

@dataclass
class TaskScope:
    task_id: str
    title: str
    acceptance_criteria: List[str]
    files_allowed: List[str] = field(default_factory=list)
    approved_by_human: bool = False
    approval_timestamp: Optional[float] = None

    def to_dict(self) -> Dict[str, Any]:
        return asdict(self)

    @classmethod
    def from_dict(cls, data: Dict[str, Any]) -> "TaskScope":
        return cls(**data)

@dataclass
class ImplementationReport:
    task_id: str
    status: str  # "SUCCESS" or "FAILED"
    summary: str
    files_changed: List[str] = field(default_factory=list)
    verification_notes: str = ""
    timestamp: float = field(default_factory=time.time)

    def to_dict(self) -> Dict[str, Any]:
        return asdict(self)

    @classmethod
    def from_dict(cls, data: Dict[str, Any]) -> "ImplementationReport":
        return cls(**data)

@dataclass
class AuditEvidence:
    automated_tests_passed: bool
    test_output: str
    security_audit_passed: bool
    browser_verification_passed: bool
    artifacts: List[str] = field(default_factory=list)
    timestamp: float = field(default_factory=time.time)

    def to_dict(self) -> Dict[str, Any]:
        return asdict(self)

    @classmethod
    def from_dict(cls, data: Dict[str, Any]) -> "AuditEvidence":
        return cls(**data)

@dataclass
class AuditReport:
    task_id: str
    verdict: str  # "PASS" or "FAIL"
    evidence: AuditEvidence
    defects: List[Dict[str, Any]] = field(default_factory=list)
    auditor_notes: str = ""
    timestamp: float = field(default_factory=time.time)

    def to_dict(self) -> Dict[str, Any]:
        return {
            "task_id": self.task_id,
            "verdict": self.verdict,
            "evidence": self.evidence.to_dict() if isinstance(self.evidence, AuditEvidence) else self.evidence,
            "defects": self.defects,
            "auditor_notes": self.auditor_notes,
            "timestamp": self.timestamp
        }

    @classmethod
    def from_dict(cls, data: Dict[str, Any]) -> "AuditReport":
        ev = data["evidence"]
        evidence_obj = AuditEvidence.from_dict(ev) if isinstance(ev, dict) else ev
        return cls(
            task_id=data["task_id"],
            verdict=data["verdict"],
            evidence=evidence_obj,
            defects=data.get("defects", []),
            auditor_notes=data.get("auditor_notes", ""),
            timestamp=data.get("timestamp", time.time())
        )

@dataclass
class StateTransition:
    from_status: str
    to_status: str
    reason: str
    timestamp: float = field(default_factory=time.time)

    def to_dict(self) -> Dict[str, Any]:
        return asdict(self)

    @classmethod
    def from_dict(cls, data: Dict[str, Any]) -> "StateTransition":
        return cls(**data)

@dataclass
class WorkflowState:
    current_task_id: Optional[str] = None
    workflow_status: WorkflowStatus = WorkflowStatus.IDLE
    approved_scope: Optional[TaskScope] = None
    remediation_attempt_count: int = 0
    max_remediations: int = 3
    implementation_report: Optional[ImplementationReport] = None
    audit_report: Optional[AuditReport] = None
    next_task_id: Optional[str] = None
    last_successful_transition: Optional[str] = None
    failure_or_blocking_reason: Optional[str] = None
    completed_tasks: List[str] = field(default_factory=list)
    history: List[StateTransition] = field(default_factory=list)

    def to_dict(self) -> Dict[str, Any]:
        return {
            "current_task_id": self.current_task_id,
            "workflow_status": self.workflow_status.value if isinstance(self.workflow_status, WorkflowStatus) else self.workflow_status,
            "approved_scope": self.approved_scope.to_dict() if self.approved_scope else None,
            "remediation_attempt_count": self.remediation_attempt_count,
            "max_remediations": self.max_remediations,
            "implementation_report": self.implementation_report.to_dict() if self.implementation_report else None,
            "audit_report": self.audit_report.to_dict() if self.audit_report else None,
            "next_task_id": self.next_task_id,
            "last_successful_transition": self.last_successful_transition,
            "failure_or_blocking_reason": self.failure_or_blocking_reason,
            "completed_tasks": self.completed_tasks,
            "history": [t.to_dict() for t in self.history]
        }

    @classmethod
    def from_dict(cls, data: Dict[str, Any]) -> "WorkflowState":
        approved_scope = TaskScope.from_dict(data["approved_scope"]) if data.get("approved_scope") else None
        imp_report = ImplementationReport.from_dict(data["implementation_report"]) if data.get("implementation_report") else None
        audit_rep = AuditReport.from_dict(data["audit_report"]) if data.get("audit_report") else None
        history = [StateTransition.from_dict(t) for t in data.get("history", [])]

        return cls(
            current_task_id=data.get("current_task_id"),
            workflow_status=WorkflowStatus(data.get("workflow_status", WorkflowStatus.IDLE.value)),
            approved_scope=approved_scope,
            remediation_attempt_count=data.get("remediation_attempt_count", 0),
            max_remediations=data.get("max_remediations", 3),
            implementation_report=imp_report,
            audit_report=audit_rep,
            next_task_id=data.get("next_task_id"),
            last_successful_transition=data.get("last_successful_transition"),
            failure_or_blocking_reason=data.get("failure_or_blocking_reason"),
            completed_tasks=data.get("completed_tasks", []),
            history=history
        )
