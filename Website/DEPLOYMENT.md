# Prescribe & Co — Deployment Guide (Hostinger)

## Server Requirements

- Hostinger Business Plan (LiteSpeed, PHP 8.4+, MySQL 8.0)
- Redis (Hostinger managed or self-hosted Predis)
- Composer 2.x
- Node.js 20+ / npm 10+

---

## Directory Structure on Hostinger

```
public_html/
├── website/          ← Laravel website app
│   └── public/       ← Document root for prescribeandco.co.uk
└── crm/              ← Laravel CRM app
    └── public/       ← Document root for crm.prescribeandco.co.uk
```

Configure two subdomains in Hostinger panel:
- `prescribeandco.co.uk` → `public_html/website/public`
- `crm.prescribeandco.co.uk` → `public_html/crm/public`

---

## Initial Deployment Steps

### 1. Upload both apps

```bash
# Via SSH or Hostinger File Manager
scp -r website/ user@server:~/public_html/website/
scp -r crm/    user@server:~/public_html/crm/
```

### 2. Install dependencies (run from each app directory)

```bash
cd ~/public_html/website
composer install --no-dev --optimize-autoloader
npm ci && npm run build

cd ~/public_html/crm
composer install --no-dev --optimize-autoloader
npm ci && npm run build
```

### 3. Configure environment files

```bash
# Copy and edit each template
cp env.website.production ~/public_html/website/.env
cp env.crm.production     ~/public_html/crm/.env

# Generate app keys
cd ~/public_html/website && php artisan key:generate
cd ~/public_html/crm     && php artisan key:generate
```

### 4. Database — run once from either app (shared DB)

```bash
cd ~/public_html/website
php artisan migrate --force
php artisan db:seed --class=DatabaseSeeder
# Seeds: treatment categories, treatments, products, GP surgeries, roles

cd ~/public_html/crm
php artisan db:seed --class=StaffSeeder
# Seeds: Super Admin account, IP whitelist localhost entry
php artisan db:seed --class=WorkflowRuleSeeder
# Seeds: all 7 system workflow rules
```

### 5. Storage symlink

```bash
cd ~/public_html/website && php artisan storage:link
# Note: CRM uses private disk only — no public symlink needed
```

### 6. Cache everything

```bash
cd ~/public_html/website
php artisan config:cache
php artisan route:cache
php artisan view:cache

cd ~/public_html/crm
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 7. SSL

Enable Let's Encrypt in Hostinger panel for both domains.

### 8. Queue workers (Supervisor)

Copy `supervisor.conf` to `/etc/supervisor/conf.d/pando.conf`, replace `your_user` with your Hostinger username, then:

```bash
supervisorctl reread
supervisorctl update
supervisorctl start pando:*
```

If Supervisor is not available on Hostinger shared hosting, use cron instead:

```bash
# In Hostinger cron manager — run every minute
* * * * * /usr/bin/php ~/public_html/crm/artisan queue:work --stop-when-empty --queue=default,mail >> /dev/null 2>&1
* * * * * /usr/bin/php ~/public_html/website/artisan queue:work --stop-when-empty --queue=default,mail >> /dev/null 2>&1
```

### 9. Scheduler

Add to Hostinger cron — run every minute:

```bash
* * * * * /usr/bin/php ~/public_html/crm/artisan schedule:run >> /dev/null 2>&1
* * * * * /usr/bin/php ~/public_html/website/artisan schedule:run >> /dev/null 2>&1
```

---

## Post-Deployment Configuration

### CRM IP Whitelist

After deployment, log in to the CRM as Super Admin and add your production office/VPN IP addresses under **Settings → IP Whitelist**, or add them directly to `CRM_IP_WHITELIST` in the CRM `.env`.

### Stripe Webhooks

In the Stripe Dashboard, configure two webhook endpoints:

1. **Website**: `https://prescribeandco.co.uk/stripe/webhook`
   - Events: `payment_intent.succeeded`, `payment_intent.payment_failed`, `invoice.payment_failed`, `invoice.payment_succeeded`, `invoice.payment_action_required`, `customer.subscription.deleted`, `customer.subscription.updated`

2. **CRM**: `https://crm.prescribeandco.co.uk/webhook/stripe`
   - Events: `payment_intent.succeeded`, `charge.refunded`, `charge.dispute.created`

Copy each webhook signing secret to the respective `.env` as `STRIPE_WEBHOOK_SECRET`.

### Super Admin First Login

The `StaffSeeder` creates a Super Admin with the password from `SUPER_ADMIN_EMAIL` and `SUPER_ADMIN_PASSWORD` in `.env`. On first login:
1. You will be prompted to set up TOTP 2FA — required before any CRM access
2. After confirming 2FA, create clinical staff accounts via **Staff → Add Staff Member**
3. Add production IP addresses to the whitelist under **Settings → IP Whitelist**

---

## Running Tests

```bash
# CRM unit and feature tests
cd ~/public_html/crm
php artisan test

# CRM tests only
php artisan test tests/Feature/CRM/

# CRM unit tests
php artisan test tests/Unit/

# Website Stripe test suite (no real Stripe account needed)
cd ~/public_html/website
php artisan test --filter=Stripe

# Full website test suite
php artisan test

# With coverage
php artisan test --coverage
```

---

## Performance Optimisation

```bash
# After any code change in production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan icons:cache   # if using Blade Icons
```

For LiteSpeed on Hostinger, ensure `.htaccess` in each `public/` directory has:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

---

## Security Checklist (Pre-Launch)

- [ ] `APP_DEBUG=false` on both apps
- [ ] `APP_ENV=production` on both apps
- [ ] `SESSION_SECURE_COOKIE=true` on both apps
- [ ] CRM `SESSION_SAME_SITE=strict`
- [ ] CRM IP whitelist populated with at least one production IP
- [ ] Stripe webhook secrets set correctly in both `.env` files
- [ ] `PHARMACY_GPHC_NUMBER` set in CRM `.env`
- [ ] Super Admin password changed from seeder default on first login
- [ ] All staff have completed 2FA setup before go-live
- [ ] SSL active on both domains (Let's Encrypt via Hostinger panel)
- [ ] `php artisan route:cache` run (prevents route enumeration)
- [ ] Private storage directory (`storage/app/private`) is not web-accessible (LiteSpeed denies access to `storage/` by default)
- [ ] Stripe test mode disabled (live keys in `.env`)
