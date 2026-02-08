# CRM System Setup Guide

Use this guide to run the legacy Zend Framework 1.12 CRM application.

## 1. Requirements

- PHP 5.6-7.1 (recommended 7.1)
- Apache with mod_rewrite
- MySQL 5.6/5.7

## 2. Database

1. Create database `db_demopsischv`.
2. Import one SQL dump from `db/` (recommended `db_psischv_09_10_24.sql`).

## 3. App Config

1. Copy:
```powershell
Copy-Item application\configs\application.local.ini.example application\configs\application.local.ini
```
2. Edit DB values in `application/configs/application.local.ini`.

## 4. Run Options

- Docker: see `README.md` quick-start section.
- XAMPP/WAMP: point document root to `CRM_System/public`.

## 5. Access

- Main app: your configured host/port
- If using Docker default: `http://localhost:8071`
