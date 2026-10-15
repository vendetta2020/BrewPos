"""
Regression and Integrity Hardening Test Suite for BrewPOS Orchestrator.
Tests all requirements from Phase 2.2:
1. Implementation report with wrong task ID is rejected.
2. Audit report with wrong task ID is rejected.
3. State remains intact after both rejections.
4. Valid report for active task can still be processed.
5. Duplicate or delayed message does not complete another task.
6. Snapshot detects existing file changes.
7. Snapshot detects new files and deleted files.
8. Snapshot detects changes to untracked files.
9. Snapshot difference cancels audit and never yields PASS (yields BLOCKED).
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
from orchestration.state_manager import StateManager
from orchestration.workflow_engine import WorkflowEngine, WorkflowEngineError
from orchestration.filesystem_snapshot import (
    take_filesystem_snapshot,
    compare_snapshots,
    record_guardrail_violation,
)

class TestOrchestratorIntegrityHardening(unittest.TestCase):
    def setUp(self):
        self.test_dir = tempfile.mkdtemp()
        self.workspace_dir = Path(self.test_dir) / "workspace"
        self.workspace_dir.mkdir(parents=True, exist_ok=True)
        self.state_file = self.workspace_dir / "orchestration" / "storage" / "orchestrator_state.json"
        self.state_file.parent.mkdir(parents=True, exist_ok=True)

        self.state_manager = StateManager(self.state_file)
        self.engine = WorkflowEngine(
            state_manager=self.state_manager,
            workspace_dir=self.workspace_dir,
            enable_snapshot_guardrail=True
        )

    def tearDown(self):
        shutil.rmtree(self.test_dir, ignore_errors=True)

    def test_01_and_02_wrong_task_id_rejected_in_both_reports(self):
        """Verify: Both implementation report and audit report with wrong task ID are rejected."""
        self.engine.request_task_approval("POS-05.1", "Barcode Migration", ["Crit 1"])
        self.engine.approve_task("POS-05.1")
        self.engine.dispatch_implementer()

        # 1. Implementation report with mismatched task_id
        wrong_imp = ImplementationReport(task_id="POS-99.9", status="SUCCESS", summary="Malicious or delayed")
        with self.assertRaises(WorkflowEngineError) as cm:
            self.engine.receive_implementation_and_trigger_qa(wrong_imp)
        self.assertIn("Task ID mismatch", str(cm.exception))

        # 2. State remains intact in IMPLEMENTING
        self.assertEqual(self.engine.state.workflow_status, WorkflowStatus.IMPLEMENTING)
        self.assertEqual(self.engine.state.current_task_id, "POS-05.1")
        self.assertIsNone(self.engine.state.implementation_report)

        # 3. Valid implementation report transitions to AUDITING
        valid_imp = ImplementationReport(task_id="POS-05.1", status="SUCCESS", summary="Legitimate implementation")
        self.engine.receive_implementation_and_trigger_qa(valid_imp)
        self.assertEqual(self.engine.state.workflow_status, WorkflowStatus.AUDITING)

        # 4. Audit report with mismatched task_id
        wrong_audit = AuditReport(
            task_id="POS-99.9",
            verdict="PASS",
            evidence=AuditEvidence(True, "OK", True, True)
        )
        with self.assertRaises(WorkflowEngineError) as cm2:
            self.engine.evaluate_audit_result(wrong_audit)
        self.assertIn("Task ID mismatch", str(cm2.exception))

        # 5. State remains intact in AUDITING
        self.assertEqual(self.engine.state.workflow_status, WorkflowStatus.AUDITING)
        self.assertEqual(self.engine.state.current_task_id, "POS-05.1")
        self.assertNotIn("POS-05.1", self.engine.state.completed_tasks)

    def test_03_state_remains_intact_after_mismatch_rejections(self):
        """Verify: State and existing reports are not mutated when mismatched reports are rejected."""
        self.engine.request_task_approval("TASK-A", "Task A", ["Criteria A"])
        self.engine.approve_task("TASK-A")
        self.engine.dispatch_implementer()

        history_len_before = len(self.engine.state.history)

        try:
            self.engine.receive_implementation_and_trigger_qa(
                ImplementationReport("TASK-B", "SUCCESS", "Wrong task")
            )
        except WorkflowEngineError:
            pass

        self.assertEqual(len(self.engine.state.history), history_len_before)
        self.assertEqual(self.engine.state.workflow_status, WorkflowStatus.IMPLEMENTING)
        self.assertIsNone(self.engine.state.implementation_report)

    def test_04_valid_report_for_active_task_processed_cleanly(self):
        """Verify: Valid report matching active task proceeds cleanly to PASS."""
        self.engine.request_task_approval("POS-05.1", "Barcode Migration", ["Crit 1"])
        self.engine.approve_task("POS-05.1")
        self.engine.dispatch_implementer()

        valid_imp = ImplementationReport("POS-05.1", "SUCCESS", "Valid code")
        self.engine.receive_implementation_and_trigger_qa(valid_imp)
        self.assertEqual(self.engine.state.workflow_status, WorkflowStatus.AUDITING)

        valid_audit = AuditReport(
            task_id="POS-05.1",
            verdict="PASS",
            evidence=AuditEvidence(True, "25 tests passed", True, True)
        )
        final_state = self.engine.evaluate_audit_result(valid_audit)
        self.assertEqual(final_state.workflow_status, WorkflowStatus.TASK_PASSED)
        self.assertIn("POS-05.1", final_state.completed_tasks)

    def test_05_duplicate_or_delayed_message_does_not_complete_another_task(self):
        """Verify: Old message from previous task cannot complete a newly active task."""
        # Complete Task 1
        self.engine.request_task_approval("TASK-1", "Task 1", ["Crit"])
        self.engine.approve_task("TASK-1")
        self.engine.dispatch_implementer()
        self.engine.receive_implementation_and_trigger_qa(ImplementationReport("TASK-1", "SUCCESS", "Done"))
        self.engine.evaluate_audit_result(AuditReport("TASK-1", "PASS", AuditEvidence(True, "OK", True, True)))
        self.assertIn("TASK-1", self.engine.state.completed_tasks)

        # Start Task 2
        self.engine.request_task_approval("TASK-2", "Task 2", ["Crit 2"])
        self.engine.approve_task("TASK-2")
        self.engine.dispatch_implementer()

        # Delayed message from TASK-1 arrives during TASK-2 implementation
        delayed_imp = ImplementationReport("TASK-1", "SUCCESS", "Late message")
        with self.assertRaises(WorkflowEngineError):
            self.engine.receive_implementation_and_trigger_qa(delayed_imp)

        # State is still implementing TASK-2, NOT completing TASK-2
        self.assertEqual(self.engine.state.current_task_id, "TASK-2")
        self.assertEqual(self.engine.state.workflow_status, WorkflowStatus.IMPLEMENTING)
        self.assertNotIn("TASK-2", self.engine.state.completed_tasks)

    def test_06_07_08_filesystem_snapshot_detection(self):
        """Verify: Snapshot accurately detects existing file modifications, additions, deletions, and untracked files."""
        # Create initial files in workspace
        file_a = self.workspace_dir / "app" / "Models" / "Test.php"
        file_a.parent.mkdir(parents=True, exist_ok=True)
        file_a.write_text("initial content", encoding="utf-8")

        file_b = self.workspace_dir / "docs" / "Untracked.md"
        file_b.parent.mkdir(parents=True, exist_ok=True)
        file_b.write_text("untracked doc", encoding="utf-8")

        snapshot_1 = take_filesystem_snapshot(self.workspace_dir)
        self.assertIn("app/Models/Test.php", snapshot_1)
        self.assertIn("docs/Untracked.md", snapshot_1)

        # Mutate 1: Modify existing file
        file_a.write_text("modified content", encoding="utf-8")

        # Mutate 2: Add new file
        file_c = self.workspace_dir / "app" / "NewFile.php"
        file_c.write_text("new file", encoding="utf-8")

        # Mutate 3: Remove file
        file_b.unlink()

        snapshot_2 = take_filesystem_snapshot(self.workspace_dir)
        diff = compare_snapshots(snapshot_1, snapshot_2)

        self.assertTrue(diff.has_changes)
        self.assertIn("app/Models/Test.php", diff.modified)
        self.assertIn("app/NewFile.php", diff.added)
        self.assertIn("docs/Untracked.md", diff.removed)

    def test_09_snapshot_difference_aborts_audit_and_blocks_pass(self):
        """Verify: Filesystem mutation during QA triggers guardrail violation, records report, and BLOCKS workflow."""
        # Set up a clean dummy app file in the temporary workspace
        code_file = self.workspace_dir / "app" / "Services" / "Sample.php"
        code_file.parent.mkdir(parents=True, exist_ok=True)
        code_file.write_text("original", encoding="utf-8")

        self.engine.request_task_approval("POS-05.1", "Barcode Migration", ["Crit"])
        self.engine.approve_task("POS-05.1")
        self.engine.dispatch_implementer()

        # Implementer reports completion -> this takes pre-audit snapshot
        imp_report = ImplementationReport("POS-05.1", "SUCCESS", "Implemented")
        self.engine.receive_implementation_and_trigger_qa(imp_report)
        self.assertEqual(self.engine.state.workflow_status, WorkflowStatus.AUDITING)

        # Simulate Rogue QA: QA illegally modifies code_file during audit!
        code_file.write_text("tampered by QA", encoding="utf-8")

        # QA returns a PASS audit report
        fake_pass_audit = AuditReport(
            task_id="POS-05.1",
            verdict="PASS",
            evidence=AuditEvidence(True, "Fake pass", True, True)
        )

        # Evaluate audit -> Guardrail MUST catch the mutation, abort PASS, and set BLOCKED!
        final_state = self.engine.evaluate_audit_result(fake_pass_audit)

        self.assertEqual(final_state.workflow_status, WorkflowStatus.BLOCKED)
        self.assertNotIn("POS-05.1", final_state.completed_tasks)
        self.assertIn("Guardrail violation", final_state.failure_or_blocking_reason)
        self.assertIn("Sample.php", final_state.failure_or_blocking_reason)

        # Verify durable violation report was written
        violations_dir = self.workspace_dir / "orchestration" / "storage" / "guardrail_violations"
        self.assertTrue(violations_dir.exists())
        violation_files = list(violations_dir.glob("violation_*.json"))
        self.assertGreater(len(violation_files), 0)

if __name__ == "__main__":
    unittest.main()
