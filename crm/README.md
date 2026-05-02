# Prescribe & Co — CRM

Internal staff CRM for Prescribe & Co online pharmacy.

**Domain:** crm.prescribeandco.co.uk  
**Stack:** Laravel 11 + Fortify, Alpine.js 3, Tailwind CSS 3  
**Auth:** Laravel Fortify (staff guard) + enforced TOTP 2FA  
**Access:** IP whitelist protected — all routes return 403 for unlisted IPs  

---

## Quick Start (Local)

```bash
composer install
npm install && npm run build
cp .env.local .env
# Set DB_DATABASE to the SAME sqlite file as the website
php artisan key:generate
php artisan migrate
php artisan serve --port=8001
```

The website must be set up first — it runs the shared database migrations.  
See **TESTING.md** for full local testing instructions and login credentials.

---

## Running Tests

```bash
php artisan test
```

---

## Deployment (Hostinger)

See **INSTALL.md** and **DEPLOYMENT.md** for full production deployment steps.

Run migrations from the **website** first (shared DB), then:
```bash
php artisan migrate --force
php artisan db:seed --class=StaffSeeder
php artisan db:seed --class=WorkflowRuleSeeder
php artisan config:cache && php artisan route:cache && php artisan view:cache
```
