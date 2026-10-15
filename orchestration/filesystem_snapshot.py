"""
Filesystem Snapshot Guardrail for BrewPOS Autonomous Orchestration.
Detects any file additions, removals, modifications, or renames during QA audit.

Important Security & Verification Boundary Note:
- Snapshot verification detects state differences between pre-audit and post-audit inspection boundaries.
- It CANNOT detect or prove the absence of in-memory or transient changes that are reverted before the post-audit inspection.
- It operates strictly within the specified workspace root; changes made outside the workspace root cannot be detected.
- It is an integrity guardrail, NOT a kernel-level read-only sandbox.
"""

import os
import hashlib
import json
import time
from pathlib import Path
from typing import Dict, List, Optional, Set
from dataclasses import dataclass, field, asdict

# Directories and patterns ignored because they are ephemeral or runtime-generated during tests
DEFAULT_IGNORE_PATTERNS = {
    ".git",
    "node_modules",
    "vendor",
    "storage/framework",
    "storage/logs",
    ".phpunit.result.cache",
    ".playwright-mcp",
    "orchestration/storage",
    "__pycache__",
    ".tmp",
}

@dataclass
class FileEntry:
    rel_path: str
    sha256: str
    size: int

@dataclass
class SnapshotDiff:
    has_changes: bool
    added: List[str] = field(default_factory=list)
    removed: List[str] = field(default_factory=list)
    modified: List[str] = field(default_factory=list)
    timestamp: float = field(default_factory=time.time)

    def summary(self) -> str:
        if not self.has_changes:
            return "No filesystem changes detected."
        parts = []
        if self.added:
            parts.append(f"Added ({len(self.added)}): {', '.join(self.added[:5])}{'...' if len(self.added) > 5 else ''}")
        if self.removed:
            parts.append(f"Removed ({len(self.removed)}): {', '.join(self.removed[:5])}{'...' if len(self.removed) > 5 else ''}")
        if self.modified:
            parts.append(f"Modified ({len(self.modified)}): {', '.join(self.modified[:5])}{'...' if len(self.modified) > 5 else ''}")
        return " | ".join(parts)

    def to_dict(self) -> Dict:
        return asdict(self)

def compute_sha256(file_path: Path) -> str:
    """Computes SHA-256 checksum of a file in binary mode."""
    h = hashlib.sha256()
    try:
        with open(file_path, "rb") as f:
            while chunk := f.read(65536):
                h.update(chunk)
        return h.hexdigest()
    except (OSError, PermissionError):
        return "ERROR_READING_FILE"

def take_filesystem_snapshot(
    root_dir: Path,
    ignore_patterns: Optional[Set[str]] = None
) -> Dict[str, str]:
    """
    Takes a snapshot of all files under root_dir (both tracked and untracked).
    Returns mapping: {relative_path: sha256_checksum}.
    """
    root = Path(root_dir).resolve()
    ignores = ignore_patterns if ignore_patterns is not None else DEFAULT_IGNORE_PATTERNS
    snapshot: Dict[str, str] = {}

    for dirpath, dirnames, filenames in os.walk(root):
        rel_dir = os.path.relpath(dirpath, root).replace("\\", "/")
        if rel_dir == ".":
            rel_dir = ""

        # Filter out ignored directories in-place to avoid descending
        dirnames[:] = [
            d for d in dirnames
            if not any(
                (f"{rel_dir}/{d}".strip("/") == ign or f"{rel_dir}/{d}".strip("/").startswith(ign + "/") or d == ign)
                for ign in ignores
            )
        ]

        for fname in filenames:
            rel_path = f"{rel_dir}/{fname}".strip("/")
            # Check if file path matches any ignore pattern
            if any(rel_path == ign or rel_path.startswith(ign + "/") or fname == ign for ign in ignores):
                continue

            full_path = Path(dirpath) / fname
            if full_path.is_file():
                snapshot[rel_path] = compute_sha256(full_path)

    return snapshot

def compare_snapshots(
    before: Dict[str, str],
    after: Dict[str, str]
) -> SnapshotDiff:
    """
    Compares two filesystem snapshots and produces a structured SnapshotDiff.
    """
    before_keys = set(before.keys())
    after_keys = set(after.keys())

    added = sorted(list(after_keys - before_keys))
    removed = sorted(list(before_keys - after_keys))
    modified = sorted([
        k for k in (before_keys & after_keys)
        if before[k] != after[k]
    ])

    has_changes = bool(added or removed or modified)
    return SnapshotDiff(
        has_changes=has_changes,
        added=added,
        removed=removed,
        modified=modified,
        timestamp=time.time()
    )

def record_guardrail_violation(
    violation_dir: Path,
    task_id: str,
    diff: SnapshotDiff,
    auditor_identity: str = "qa_auditor_agent"
) -> Path:
    """
    Saves a durable guardrail violation record to disk for human inspection.
    Does NOT auto-revert or delete files.
    """
    violation_dir.mkdir(parents=True, exist_ok=True)
    report_file = violation_dir / f"violation_{task_id}_{int(time.time())}.json"
    data = {
        "task_id": task_id,
        "auditor_identity": auditor_identity,
        "timestamp": time.time(),
        "violation_type": "UNAUTHORIZED_FILESYSTEM_MUTATION_DURING_QA",
        "diff": diff.to_dict(),
        "instruction": "Do not auto-revert. Inspect files manually and resolve before proceeding."
    }
    with open(report_file, "w", encoding="utf-8") as f:
        json.dump(data, f, indent=2)
    return report_file
