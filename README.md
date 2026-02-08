# CRM_System (Zend Framework 1.12)

Legacy Student CRM/School system based on Zend Framework 1.12.

## Runtime Requirements

- PHP `5.6` to `7.1` (recommended: `7.1`)
- Apache with `mod_rewrite`
- MySQL `5.6/5.7` (or compatible MariaDB)
- PHP extensions: `pdo_mysql`, `mysqli`, `mbstring`

This codebase is not compatible with PHP 8.x without migration work.

## Project Structure

- `application/` Zend modules, controllers, models, views
- `library/` Zend Framework 1.12 core
- `public/` web root (Apache/Nginx must point here)
- `db/` database dumps

## Quick Start (Docker)

1. Copy environment defaults:
```powershell
Copy-Item .env.example .env
```
2. Start containers:
```powershell
docker compose up --build -d
```
3. Create local override config:
```powershell
Copy-Item application\configs\application.local.ini.example application\configs\application.local.ini
```
4. Edit `application/configs/application.local.ini` for Docker DB host:
```ini
resources.db.params.host = "db"
resources.db.params.username = "root"
resources.db.params.password = ""
resources.db.params.dbname = "db_demopsischv"
```
5. Import database (pick one dump, recommended below):
```powershell
Get-Content db\db_psischv_09_10_24.sql | docker exec -i crm_legacy_db mysql -uroot db_demopsischv
```
6. Open:
- App: `http://localhost:8071`
- phpMyAdmin: `http://localhost:8081`

## Quick Start (XAMPP/WAMP)

1. Use PHP 7.1 (or 5.6-7.1).
2. Point virtual host to `<project>/public`.
3. Create DB `db_demopsischv`.
4. Import `db/db_psischv_09_10_24.sql`.
5. Copy `application/configs/application.local.ini.example` to `application/configs/application.local.ini` and set DB credentials.

## Important Notes

- `public/index.php` now supports optional local config merging via:
  - `application/configs/application.ini` (base)
  - `application/configs/application.local.ini` (local override, optional)
- Root `index.php` only redirects to `/public` and is not the runtime entrypoint.
