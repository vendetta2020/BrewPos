# BrewPOS Autonomous Orchestration Infrastructure

This package implements the autonomous **Implementer–QA/Auditor** workflow with a strict **Human Approval Gate** before every new task, ensuring total adherence to `docs/AUTONOMOUS_DOD_BACKLOG.md`.

---

## 1. Directory Structure

```
orchestration/
├── __init__.py                  # Package root
├── state_schema.py              # Strongly-typed dataclasses for WorkflowState, Scope, Reports & Audit
├── state_manager.py             # Concurrency-safe, atomic disk persistence with fsync and recovery
├── subagent_contracts.py        # System prompts, skill path injections, and JSON contract schemas
├── workflow_engine.py           # State machine controlling approval, dispatch, QA triggers, and retries
├── storage/
│   └── orchestrator_state.json  # Authoritative, durable JSON state
├── tests/
│   └── test_control_flow.py     # 7 automated control-flow verification tests (100% PASS)
└── README.md                    # Setup, invocation, and operational guide
```

---

## 2. State Machine Lifecycle

```
[IDLE] 
  │
  ▼
[AWAITING_HUMAN_APPROVAL]  <── (Present task scope & acceptance criteria)
  │ (User grants explicit approval)
  ▼
[APPROVED]
  │ (Auto-dispatch)
  ▼
[IMPLEMENTING]             <── (implementer_agent executes task)
  │ (Auto-handoff via send_message)
  ▼
[AUDITING]                 <── (qa_auditor_agent runs 4-pillar audit)
  │
  ├─── If verdict == PASS ──────► [TASK_PASSED] ──► (Pause for next task approval)
  │                                     │
  │                               (All Done?) ──► [ALL_DOD_COMPLETE]
  │
  └─── If verdict == FAIL
         │
         ├── Retries <= 3 ──────► [REMEDIATING] ──► (Auto-dispatch to Implementer with defects)
         │                                               │
         │                                               └──► (Auto re-audit)
         │
         └── Retries > 3  ──────► [BLOCKED] ──► (Request human intervention)
```

---

## 3. Subagent Specifications & Registered Permissions

Both subagents are registered in the active Antigravity session using `define_subagent`:

| Subagent Name | Role | Write Tools | MCP Tools | Subagent Tools | Constraints |
| :--- | :--- | :---: | :---: | :---: | :--- |
| `implementer_agent` | Code Implementer | **TRUE** | **TRUE** | FALSE | May edit application code within approved task scope. Must consult `ui-ux-pro-max` and `design.md`. |
| `qa_auditor_agent` | Independent QA & Auditor | **TRUE** (Shell only) | **TRUE** (Playwright) | FALSE | **Strictly read-only for application code**. Write tools are enabled exclusively to run `docker compose exec app php artisan test`. Code modifications are strictly forbidden by prompt and monitored by git diff. |

---

## 4. Verification Results

All 7 core control-flow requirements were verified via automated tests in `orchestration/tests/test_control_flow.py`:
1. `test_01_approved_task_triggers_implementer`: **PASSED**
2. `test_02_implementer_completion_triggers_qa_without_manual_prompting`: **PASSED**
3. `test_03_fail_triggers_remediation_and_subsequent_audit`: **PASSED**
4. `test_04_pass_stops_loop_and_requests_approval_for_next_task`: **PASSED**
5. `test_05_three_unsuccessful_remediation_attempts_lead_to_blocked`: **PASSED**
6. `test_06_missing_or_ambiguous_evidence_never_yields_pass`: **PASSED**
7. `test_07_restarting_orchestrator_does_not_lose_task_state`: **PASSED**

Native subagent integration was separately verified live:
- `implementer_agent` was invoked with smoke test prompt $\to$ returned structured JSON completion report via `send_message`.
- `qa_auditor_agent` was invoked with audit prompt $\to$ independently executed `docker compose exec app php artisan test` inside the Docker container (25 passed tests, 117 assertions) $\to$ returned structured JSON audit report via `send_message`.

---

## 5. Remaining Limitations & Technical Nuances

1. **Write Tools Granularity in Antigravity App**:
   - In Antigravity's `define_subagent`, the flag `enable_write_tools: true` bundles shell command execution (`run_command`) with file editing tools (`write_to_file`, `replace_file_content`).
   - *Safe Alternative Implemented*: `qa_auditor_agent` has write tools enabled to allow running `docker compose exec app php artisan test`, but its system prompt strictly forbids calling file editing tools. The orchestrator tracks file modification diffs to guarantee no code files are touched by QA.
2. **Subagent Skill Inheritance**:
   - Subagents do not automatically inherit the `<skills>` metadata block from the parent agent.
   - *Solution Implemented*: Subagent system prompts and task prompts explicitly inject absolute paths to all relevant skill files (`C:\Users\asus\.agents\skills\ui-ux-pro-max\SKILL.md`, etc.).
3. **Session Lifetime**:
   - In-memory subagent definitions via `define_subagent` persist for the duration of the Antigravity conversation session. If a completely new chat session is started, `subagent_contracts.py` re-registers them instantly. State persistence in `orchestrator_state.json` survives across sessions.
