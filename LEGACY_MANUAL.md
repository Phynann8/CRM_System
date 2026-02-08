# 📜 Legacy Manual: CRM System (Student Management)

## 1. System Overview
**Type**: Legacy Web Application (2016 Era)
**Stack**: PHP 5.6 / 7.1 (Zend Framework 1.12), MySQL, jQuery/Dojo
**Original Name**: PSIS (Possible Student Information System)
**Status**: ⚠️ **Legacy / Archival**. Do not run on PHP 8.0+.

## 2. Directory Structure
```bash
/
├── application/               # Zend Application Core
│   ├── configs/application.ini # Database & Environment Config
│   ├── modules/               # HMVC Modules
│   │   ├── accounting/        # Fee/Payment Logic
│   │   ├── foundation/        # School Setup (Students, Groups)
│   │   ├── global/            # Common Utilities
│   │   └── home/              # Dashboard
│   └── Bootstrap.php          # App Entry Point
├── public/                    # Web Root
│   ├── index.php              # Front Controller
│   └── css/js/images          # Assets
├── library/                   # Zend Framework Core Files
└── db/                        # Database Dumps
    └── db_psischv_09_10_24.sql # Recommended Schema Restore File
```

## 3. Database Schema
**Database Name**: `db_demopsischv` (Configured in `application.ini`)

### Key Tables (Inferred)
*   **Location Data**: `ln_commune`, `ln_district` (System has heavy GIS support).
*   **Foundation Module**: Tables related to `students` (rms_student?), `groups`, `academic_year`.
*   **Accounting Module**: Tables for `invoices`, `payments`, `service_types`.
*   **Issue Module**: Likely tracks `discipline_scores` or `attendance`.

## 4. Configuration & Setup
> [!WARNING]
> This system requires a legacy environment. Recommended to use Docker.

1.  **Database Connection**:
    Edit `application/configs/application.ini`:
    ```ini
    resources.db.params.host = localhost
    resources.db.params.username = root
    resources.db.params.password = 
    resources.db.params.dbname = db_demopsischv
    ```

2.  **Virtual Host**:
    Point your Apache/Nginx document root to the `/public` folder, NOT the project root.
    ```apache
    <VirtualHost *:80>
        DocumentRoot "E:/Website_Dev/CRM_System/public"
        ServerName crm.local
    </VirtualHost>
    ```

3.  **Modules & Routing**:
    Zend splits logic into modules.
    *   URL Pattern: `/module/controller/action`
    *   Example: `/accounting/fee/index` -> `application/modules/accounting/controllers/FeeController.php`

## 5. Troubleshooting
*   **"Zend_Application_Bootstrap_Bootstrap not found"**: Ensure `library/` is in your `include_path`.
*   **Database Error**: Check if `application.ini` matches your MySQL credentials.
*   **Blank Page**: Enable errors in `.htaccess` or `index.php` (set `display_errors = 1`).
