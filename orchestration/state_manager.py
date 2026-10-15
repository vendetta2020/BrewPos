"""
Durable and Concurrency-Safe State Manager for BrewPOS Orchestrator.
Guarantees atomic persistence, validation against invalid transitions, and recovery.
"""

import os
import json
import time
from pathlib import Path
from typing import Optional, Dict, Any
from .state_schema import WorkflowState, WorkflowStatus, StateTransition, AuditReport, ImplementationReport, TaskScope

DEFAULT_STATE_FILE = Path(__file__).parent / "storage" / "orchestrator_state.json"

class StateManagerError(Exception):
    """Raised when an invalid state transition or corruption is encountered."""
    pass

class StateManager:
    def __init__(self, state_file: Optional[Path] = None):
        self.state_file = Path(state_file) if state_file else DEFAULT_STATE_FILE
        self.state_file.parent.mkdir(parents=True, exist_ok=True)
        self.current_state = self._load_or_initialize()

    def _load_or_initialize(self) -> WorkflowState:
        """Loads existing state from disk or initializes a clean durable state."""
        if not self.state_file.exists():
            clean_state = WorkflowState()
            self._save_atomic(clean_state)
            return clean_state

        try:
            with open(self.state_file, "r", encoding="utf-8") as f:
                data = json.load(f)
            return WorkflowState.from_dict(data)
        except Exception as e:
            # Corruption recovery: backup corrupted file and initialize recovery state
            backup_file = self.state_file.with_suffix(f".corrupt.{int(time.time())}.json")
            if self.state_file.exists():
                os.replace(self.state_file, backup_file)
            recovered = WorkflowState(
                failure_or_blocking_reason=f"Recovered from corrupted state. Backup saved to {backup_file.name}. Error: {str(e)}"
            )
            self._save_atomic(recovered)
            return recovered

    def _save_atomic(self, state: WorkflowState) -> None:
        """
        Atomically persists state using a temporary file and atomic rename (os.replace).
        Prevents corrupted JSON on sudden crash or process termination.
        """
        self._validate_state_integrity(state)

        tmp_file = self.state_file.with_suffix(f".tmp.{os.getpid()}.{int(time.time() * 1000)}")
        try:
            with open(tmp_file, "w", encoding="utf-8") as f:
                json.dump(state.to_dict(), f, indent=2)
                f.flush()
                os.fsync(f.fileno())
            os.replace(tmp_file, self.state_file)
        finally:
            if tmp_file.exists():
                try:
                    tmp_file.unlink()
                except OSError:
                    pass

    def _validate_state_integrity(self, state: WorkflowState) -> None:
        """
        Non-negotiable validation rule:
        Never infer PASS from missing state or reports.
        Missing or ambiguous evidence never yields PASS.
        """
        if state.workflow_status == WorkflowStatus.TASK_PASSED:
            if not state.audit_report:
                raise StateManagerError("Cannot transition to TASK_PASSED without an audit report.")
            if state.audit_report.verdict != "PASS":
                raise StateManagerError(f"Cannot transition to TASK_PASSED with audit verdict: {state.audit_report.verdict}")
            if not state.audit_report.evidence or not state.audit_report.evidence.automated_tests_passed:
                raise StateManagerError("Cannot transition to TASK_PASSED without verified automated tests evidence.")

    def reload(self) -> WorkflowState:
        """Reloads the state from disk to catch external or recovered changes."""
        self.current_state = self._load_or_initialize()
        return self.current_state

    def transition(self, to_status: WorkflowStatus, reason: str, **kwargs) -> WorkflowState:
        """
        Performs a recorded, validated state transition and persists atomically.
        """
        old_status = self.current_state.workflow_status.value
        self.current_state.workflow_status = to_status

        # Apply optional updates
        for key, value in kwargs.items():
            if hasattr(self.current_state, key):
                setattr(self.current_state, key, value)

        transition_record = StateTransition(
            from_status=old_status,
            to_status=to_status.value,
            reason=reason,
            timestamp=time.time()
        )
        self.current_state.history.append(transition_record)
        self.current_state.last_successful_transition = f"{old_status} -> {to_status.value} ({reason})"

        self._save_atomic(self.current_state)
        return self.current_state

    def get_state(self) -> WorkflowState:
        return self.current_state
