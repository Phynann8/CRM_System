# Database Search
Checking for rms_student table...

## 📌 System Overview
The folder `CRM_System` contains the source code for a **Student Management System** (likely an older version of PSIS).
It is built on a **Legacy PHP Framework** and is **not** a static HMTL/JS website.

## 🛠 Tech Stack
*   **Language**: PHP (Legacy, likely 5.6 or 7.0)
*   **Framework**: **Zend Framework 1.12** (Identified by directory structure and `Zend_Application` usage).
*   **Database**: MySQL (PDO_MYSQL)
*   **Frontend**: jQuery, Dojo Toolkit (legacy JS frameworks seen in the `public` folder).

## ⚙️ Configuration (from `application.ini`)
*   **Database Name**: `db_demopsischv`
*   **Default User**: `root` (No password set in config)
*   **Environment**: Production/Development modes defined.
*   **Modules**: The system is modular (folder `application/modules` exists).

## ⚠️ Important Notes
1.  **Not Run-able Directly**: You cannot open `index.html` to run this. It requires a PHP Server.
2.  **Server Requirements**: 
    *   Apache/Nginx Web Server.
    *   **PHP Version**: **5.6 to 7.1** (Zend Framework 1 is incompatible with PHP 8.0+).
    *   **MySQL Database**: ✅ **FOUND**. Use `db/db_psischv_09_10_24.sql` (or similar) to restore the `db_demopsischv` database.
3.  **Security**:
    *   `SECRET_KEY` is hardcoded in `public/index.php`.
    *   Database credentials are in `application/configs/application.ini`.

## 📁 Directory Structure
*   `application/`: Core PHP Logic (Controllers, Models, Views).
*   `library/`: Zend Framework core files.
*   `public/`: The Web Root (CSS, JS, Images, `index.php`). **Point your web server here.**
*   `index.php` (Root): Just a redirect to `/public`.

## 🚀 How to Run (Local)
1.  Install **XAMPP** or **WAMP** (Older version with PHP 7.x recommended).
2.  Point the Virtual Host Document Root to `e:\Website_Development\CRM_System\public`.
3.  Create a MySQL database named `db_demopsischv`.
4.  Import the database schema (Look for a `.sql` file in the project).
5.  Access via browser (e.g., `localhost/crm`).
