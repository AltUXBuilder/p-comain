# Prescribe & Co — Website

Patient-facing e-commerce website for Prescribe & Co online pharmacy.

**Domain:** prescribeandco.co.uk  
**Stack:** Laravel 11, Livewire 3, Alpine.js 3, Tailwind CSS 3  
**Auth:** Laravel Breeze (email/password) + triggered 2FA via email OTP  

---

## Quick Start (Local)

```bash
composer install
npm install && npm run build
cp .env.local .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan db:seed
php artisan db:seed --class=LocalTestSeeder
php artisan storage:link
php artisan serve --port=8000
```

See **TESTING.md** for full local testing instructions and login credentials.

---

## Running Tests

```bash
php artisan test                          # full suite
php artisan test --filter=Stripe          # Stripe suite only
php artisan test --coverage               # with coverage
```

All tests use SQLite in-memory and mock all Stripe/SendGrid calls. No API keys needed.

---

## Deployment (Hostinger)

See **INSTALL.md** and **DEPLOYMENT.md** for full production deployment steps.

Key commands:
```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan db:seed
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

---

## Environment

Copy `.env.example` to `.env` and fill in:
- `APP_KEY` — generate with `php artisan key:generate`
- `DB_*` — shared MySQL database credentials
- `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`
- `SENDGRID_API_KEY`
- `PHARMACY_GPHC_NUMBER`

For local development, use `.env.local` instead — uses SQLite, log mailer, no API keys required.
