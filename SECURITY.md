# Security Notes (Legacy CRM)

This project is legacy software and should be treated as high-risk if exposed publicly.

## Supported Runtime

- PHP 5.6-7.1 only (legacy compatibility target)
- Not supported on PHP 8.x

## Minimum Hardening Checklist

1. Do not deploy with default DB credentials.
2. Keep `application/configs/application.local.ini` out of version control.
3. Restrict network access to the app and database (VPN or internal network).
4. Force HTTPS at reverse proxy/web server.
5. Rotate `SECRET_KEY` in `public/index.php` for each deployment.
6. Disable verbose errors in production (`display_errors = Off`).
7. Backup DB regularly and test restore.

## Reporting Security Issues

Share details privately with maintainers. Include:
- affected file/module
- reproduction steps
- impact scope
