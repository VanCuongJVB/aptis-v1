# APTIS Lite Starter (v3)

Features:
- Admin Students: list, create, edit, delete
- **Quick extend**: +30d / +90d
- **CSV import** (xlsx optional if you install `maatwebsite/excel`)
- **2-device limit** per user with auto revoke and `access_logs`
- Quizzes/Attempts basic flow; listening controls

## Install
1) Drop into your Laravel project root (overwrite if prompted).
2) Run migrations & seeders:
```bash
php artisan migrate --path=database/migrations/aptis
php artisan db:seed --class=Database\Seeders\DatabaseSeeder
```
> If using DB sessions:
```bash
php artisan session:table && php artisan migrate
```

3) Login:
- Admin: `admin@example.com` / `123456`
- Student: `student@example.com` / `123456`

## CSV format
Header row:
```
email,name,is_active,access_starts_at,access_ends_at
alice@example.com,Alice,1,2025-09-01 00:00,2025-10-01 00:00
bob@example.com,Bob,1,,
```
