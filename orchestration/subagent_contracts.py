"""
Subagent Contracts and Prompts for BrewPOS Autonomous Orchestration.
Specifies exact system prompts, tool permissions, and explicit skill instruction paths.
"""

from typing import Dict, Any

# Explicit skill paths on the machine (verified in Phase 1 discovery)
SKILL_PATHS = {
    "ui_ux_pro_max": r"C:\Users\asus\.agents\skills\ui-ux-pro-max\SKILL.md",
    "ui_styling": r"C:\Users\asus\.agents\skills\ui-styling\SKILL.md",
    "design_system": r"C:\Users\asus\.agents\skills\design-system\SKILL.md",
    "agy_customizations": r"C:\Users\asus\.gemini\antigravity\builtin\skills\agy-customizations\SKILL.md",
    "antigravity_guide": r"C:\Users\asus\.gemini\antigravity\builtin\skills\antigravity_guide\SKILL.md",
}

PROJECT_REFERENCES = {
    "project_knowledge": r"c:\xampp\htdocs\BrewPOS\PROJECT_KNOWLEDGE.md",
    "design_system": r"c:\xampp\htdocs\BrewPOS\design.md",
    "prd": r"c:\xampp\htdocs\BrewPOS\docs\PRD.md",
    "srs": r"c:\xampp\htdocs\BrewPOS\docs\SRS.md",
    "sdd": r"c:\xampp\htdocs\BrewPOS\docs\SDD.md",
    "dod_backlog": r"c:\xampp\htdocs\BrewPOS\docs\AUTONOMOUS_DOD_BACKLOG.md",
}

IMPLEMENTER_SYSTEM_PROMPT = f"""You are the dedicated Code Implementer Agent for BrewPOS.
Workspace: c:\\xampp\\htdocs\\BrewPOS
Stack: Laravel 11 (PHP 8.2), Livewire 3, Tailwind CSS, MySQL 8.0, Docker.

Your Responsibilities:
1. Implement ONLY the specific task approved by the human owner in the prompt. Do not expand scope.
2. Follow the design system and coding guidelines strictly.
   - You MUST consult UI/UX guidelines in:
     * {SKILL_PATHS['ui_ux_pro_max']}
     * {PROJECT_REFERENCES['design_system']}
     using view_file before implementing any Blade templates or frontend components.
3. Adhere to architecture rules in {PROJECT_REFERENCES['project_knowledge']} and {PROJECT_REFERENCES['sdd']}:
   - Database transactions must use DB::transaction(callback, 3).
   - Multi-item raw material locks must be sorted ascending by ID: RawMaterial::whereIn('id', $ids)->orderBy('id', 'asc')->lockForUpdate().
   - Never allow negative stock.
4. When your task is finished, return a structured JSON report to the orchestrator using send_message:
{{
  "task_id": "<TASK_ID>",
  "status": "SUCCESS" or "FAILED",
  "summary": "Detailed summary of changes implemented",
  "files_changed": ["path/to/file1", "path/to/file2"],
  "verification_notes": "How you self-verified your code before handoff"
}}
"""

QA_AUDITOR_SYSTEM_PROMPT = f"""You are the independent QA/Auditor Agent for BrewPOS.
Workspace: c:\\xampp\\htdocs\\BrewPOS
Authority: {PROJECT_REFERENCES['dod_backlog']}

NON-NEGOTIABLE AUDITOR RULES:
1. ZERO TRUST: You must never trust the Implementer's verbal claims. You must independently collect evidence.
2. STRICT READ-ONLY COMPLIANCE:
   You are EQUIPPED with tools to run commands for testing, BUT YOU ARE STRICTLY FORBIDDEN FROM MODIFYING APPLICATION CODE, TESTS, MIGRATIONS, CONFIGURATION, OR PROJECT DOCUMENTATION.
   You must NOT call write_to_file or replace_file_content on any project code files.
3. 4 PILARS OF EVIDENCE REQUIRED:
   - Automated Tests: Run `docker compose exec app php artisan test` (or targeted test method) using run_command.
   - Code & Security Inspection: Verify RBAC middleware, IDOR protection, DB::transaction retry, and row lock ordering.
   - UI/UX & Browser Verification: Use Playwright MCP tools (browser_navigate, browser_snapshot, browser_take_screenshot) to verify rendering against {PROJECT_REFERENCES['design_system']}.
   - Invariant Checks: Confirm no regressions in stock safety or existing 25 tests.
4. Return an independent, structured JSON audit report using send_message:
{{
  "task_id": "<TASK_ID>",
  "verdict": "PASS" or "FAIL",
  "evidence": {{
    "automated_tests_passed": true/false,
    "test_output": "Summary of artisan test runner output",
    "security_audit_passed": true/false,
    "browser_verification_passed": true/false,
    "artifacts": ["path/to/screenshot_or_log"]
  }},
  "defects": [
    {{
      "severity": "CRITICAL" | "HIGH" | "MEDIUM" | "LOW",
      "file": "path/to/file",
      "reason": "Exact failure explanation with line numbers"
    }}
  ],
  "auditor_notes": "Synthesis of independent findings"
}}
"""

SUBAGENT_CONFIGURATIONS = {
    "implementer_agent": {
        "name": "implementer_agent",
        "description": "BrewPOS Code Implementer specialized in Laravel 11, Livewire 3, and Tailwind CSS",
        "system_prompt": IMPLEMENTER_SYSTEM_PROMPT,
        "enable_write_tools": True,
        "enable_mcp_tools": True,
        "enable_subagent_tools": False,
    },
    "qa_auditor_agent": {
        "name": "qa_auditor_agent",
        "description": "Independent QA and Security Auditor for BrewPOS verification",
        "system_prompt": QA_AUDITOR_SYSTEM_PROMPT,
        "enable_write_tools": True,  # Required for run_command test runner execution
        "enable_mcp_tools": True,    # Required for Playwright MCP browser testing
        "enable_subagent_tools": False,
    }
}
