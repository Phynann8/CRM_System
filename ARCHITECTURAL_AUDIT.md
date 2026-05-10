# Architectural Audit: CRM_System

**Date:** 2026-02-15
**Target:** `CRM_System` (Legacy PHP / Zend Framework)
**Auditor:** Principal Systems Architect

## 1) Executive Summary
**Architecture:** Server-Side MVC Monolith.
**Verdict:** **Legacy / End-of-Life (EOL).**
This system is built on **Zend Framework 1.12**, which reached End-of-Life in 2016. It likely requires PHP 5.6 or 7.0, which are also EOL and receive no security updates. Running this in production today is a massive security risk.

## 2) Key Design Decisions & Analysis

### Technology Stack
- **Framework:** Zend Framework 1.12.
- **Language:** PHP (Legacy).
- **Database:** MySQL.
- **Frontend:** jQuery / Dojo Toolkit.

### Security Architecture (STRIDE)
#### 🔴 Critical Risks
1.  **Vulnerable Components:**
    -   *Threat:* The framework and PHP runtime have varying known unpatched vulnerabilities (CVEs).
    -   *Mitigation:* **Do not expose to the internet.** Containerize immediately if internal access is required, using an isolated network.

2.  **Hardcoded Secrets:**
    -   *Threat:* Database credentials and `SECRET_KEY` are found in `application/configs/application.ini` and public scripts.
    -   *Mitigation:* Use Environment Variables (`.env`) even in legacy apps if possible, or move config outside web root.

## 3) Recommendations
- **Avoid Development:** Do not build new features on this stack.
- **Migration:**
    -   **Option A (Rewrite):** Re-implement using the `CRM_EFCore` (.NET 8) or `brew_Coffee` (React/Node) patterns.
    -   **Option B (Isolate):** If archival is needed, wrap in a Docker container with PHP 5.6 and never expose port 80 to the public.
