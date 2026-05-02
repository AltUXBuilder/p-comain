# Local Testing Guide — Prescribe & Co

## You do NOT need

- A Stripe account
- A SendGrid account
- Redis
- MySQL (SQLite works fine)
- Any paid API keys

Everything below runs entirely on your local machine.

---

## Quick Start (5 minutes)

### 1. Set up the website

```bash
cd website

# Install dependencies
composer install
npm install && npm run build

# Copy the local env file
cp .env.local .env

# Generate app key
php artisan key:generate

# Create SQLite database file
touch database/database.sqlite

# Run all migrations
php artisan migrate

# Seed products, staff, and GP surgeries
php artisan db:seed

# Seed test patient, orders, consultations (no Stripe needed)
php artisan db:seed --class=LocalTestSeeder

# Create storage symlink
php artisan storage:link

# Start the dev server
php artisan serve --port=8000
```

Visit: **http://localhost:8000**

---

### 2. Set up the CRM

Open a second terminal:

```bash
cd crm

composer install
npm install && npm run build

cp .env.local .env

# Edit .env — set DB_DATABASE to the SAME sqlite file path as the website
# Example: DB_DATABASE=/Users/yourname/pando/website/database/database.sqlite

php artisan key:generate

# CRM migrations (adds CRM-only tables to the shared DB)
php artisan migrate

php artisan serve --port=8001
```

Visit: **http://localhost:8001**

---

## Login Credentials (created by LocalTestSeeder)

| App | Role | Email | Password |
|-----|------|-------|----------|
| Website | Patient | test@prescribeandco.test | Password1! |
| CRM | Super Admin | super@crm.test | Password1! |
| CRM | Superintendent | super.pharm@crm.test | Password1! |
| CRM | Prescriber | prescriber@crm.test | Password1! |
| CRM | Dispenser | dispenser@crm.test | Password1! |
| CRM | Support | support@crm.test | Password1! |
| CRM | Finance | finance@crm.test | Password1! |

> **2FA is disabled** on all test accounts. You can log straight in.
> The CRM IP whitelist allows 127.0.0.1 and ::1 by default.

---

## Full Order Flow Test (end to end)

This walks through the complete patient → CRM workflow using only local test data.

### Step 1 — Patient submits a consultation

1. Go to **http://localhost:8000**
2. Browse to any treatment (e.g. Weight Loss → Mounjaro)
3. Click **Start Consultation**
4. Answer the questionnaire questions
5. At the gate, log in as `test@prescribeandco.test` / `Password1!`
6. Consultation is submitted → status: **Awaiting Review**

*Email would normally be sent here. With `MAIL_MAILER=log` it's written to `storage/logs/laravel.log` instead. Check it with:*
```bash
tail -f website/storage/logs/laravel.log
```

---

### Step 2 — Prescriber reviews in the CRM

1. Go to **http://localhost:8001**
2. Log in as `prescriber@crm.test` / `Password1!`
3. Go to **Consultations → Queue**
4. Find the consultation and click **Review**
5. Click **Approve** → prescription is generated automatically
6. The prescription PDF is created in `crm/storage/app/private/prescriptions/`

---

### Step 3 — Dispenser picks up and labels

1. Still in CRM, log in as `dispenser@crm.test` / `Password1!`
2. Go to **Dispensing → Queue**
3. Find the prescription — click **Dispense**
4. Select the stock batch (BATCH-TEST-001 created by LocalTestSeeder)
5. Generate the 70×35mm label PDF
6. Click **Sign Off** → order moves to Processing

---

### Step 4 — Dispatch the order

1. Go to **Orders** in CRM
2. Find the processing order
3. Click **Dispatch**
4. Enter any tracking number (e.g. `AB999999999GB`) and select Royal Mail
5. Order status → **Dispatched**
6. Patient dispatch email is written to `storage/logs/laravel.log`

---

### Step 5 — Patient sees their order

1. Go back to **http://localhost:8000**
2. Log in as `test@prescribeandco.test`
3. Go to **Account → Orders**
4. Order shows as dispatched with tracking number

---

## Testing Stripe Payments (when you're ready)

You don't need a paid Stripe account. The free Stripe account (stripe.com) gives you test mode.

### Get free Stripe test keys

1. Sign up at **https://stripe.com** (free, no credit card needed)
2. In the dashboard, make sure you are in **Test Mode** (toggle top right)
3. Go to **Developers → API Keys**
4. Copy your `pk_test_...` and `sk_test_...` keys

### Add to both .env files

```env
STRIPE_KEY=pk_test_YOUR_KEY_HERE
STRIPE_SECRET=sk_test_YOUR_KEY_HERE
```

### Test card numbers (all use expiry 12/99, CVC 123)

| Scenario | Card number |
|----------|-------------|
| ✅ Payment succeeds | 4242 4242 4242 4242 |
| ✅ UK Visa debit | 4000 0082 6000 0000 |
| ❌ Card declined | 4000 0000 0000 0002 |
| ❌ Insufficient funds | 4000 0000 0000 9995 |
| 🔐 Requires 3D Secure | 4000 0025 0000 3155 |

### Test webhooks locally (optional)

Install the free Stripe CLI, then:

```bash
stripe listen --forward-to http://localhost:8000/webhooks/stripe
```

The CLI prints a webhook signing secret — add it as `STRIPE_WEBHOOK_SECRET` in `.env`.

---

## Testing Email (when you're ready)

You don't need a SendGrid account to test locally. Two free options:

### Option A — Mailtrap (recommended, free tier)

1. Sign up free at **https://mailtrap.io**
2. Go to Email Testing → Inboxes → your inbox → SMTP Settings
3. Select **Laravel** from the integrations dropdown
4. Copy the settings into `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
```

All emails are caught and displayed in the Mailtrap inbox — nothing goes to real addresses.

### Option B — Log driver (already set up, no account needed)

The `.env.local` files already have `MAIL_MAILER=log`. Every email is written to the log file.

```bash
# See all emails in real time
tail -f storage/logs/laravel.log | grep -A 20 "Subject:"
```

### When ready for SendGrid (production)

1. Sign up free at **https://sendgrid.com** (100 emails/day free)
2. Go to Settings → API Keys → Create API Key
3. Add to `.env`:
```env
MAIL_MAILER=sendgrid
SENDGRID_API_KEY=SG.YOUR_KEY_HERE
```

---

## Running the Test Suite

```bash
cd website

# All tests (mocked — no real Stripe/SendGrid calls)
php artisan test

# Just Stripe tests
php artisan test --filter=Stripe

# With coverage report
php artisan test --coverage
```

The test suite uses SQLite in-memory and mocks all outbound API calls. It will pass with empty `.env` keys.

---

## Pre-built Test Data (from LocalTestSeeder)

After running `php artisan db:seed --class=LocalTestSeeder` you have:

| Data | Detail |
|------|--------|
| Patient account | test@prescribeandco.test |
| Consultation (awaiting review) | Ready to approve in CRM |
| Consultation (approved) | Already reviewed by prescriber |
| Prescription | RX-{today}-TEST1, status: approved |
| Order #1 (processing) | Ready to dispatch in CRM |
| Order #2 (dispatched) | Royal Mail, tracking AB123456789GB |
| Invoice | INV-{year}-00001, paid |
| Stock batch | 97 units, expires in 1 year |
| Stock | Product stocked and above threshold |
| Message | Unread message from patient in CRM inbox |
| Staff × 6 | One per role, all 2FA-disabled for easy login |
| IP whitelist | 127.0.0.1 and ::1 allowed |

---

## Creating More Test Orders Manually

To create additional test orders without going through the full consultation flow:

```bash
php artisan tinker
```

```php
// Create a quick test order
$patient = \App\Models\User::where('email', 'test@prescribeandco.test')->first();
$product = \App\Models\Product::where('active', true)->first();

\App\Models\Order::create([
    'order_number'           => 'ORD-' . date('Y') . '-' . rand(10000, 99999),
    'user_id'                => $patient->id,
    'status'                 => 'processing',
    'subtotal'               => 149.00,
    'vat_amount'             => 0.00,
    'shipping_cost'          => 0.00,
    'total'                  => 149.00,
    'currency'               => 'GBP',
    'payment_method'         => 'card',
    'stripe_payment_intent_id' => 'pi_test_' . uniqid(),
    'stripe_charge_id'       => 'ch_test_' . uniqid(),
    'requires_cold_chain'    => false,
    'delivery_name'          => $patient->full_name,
    'delivery_address_line_1' => $patient->address_line_1,
    'delivery_city'          => $patient->city,
    'delivery_postcode'      => $patient->postcode,
    'delivery_country'       => 'GB',
]);
```

Or re-run the seeder as many times as you like (it creates new records each run):

```bash
php artisan db:seed --class=LocalTestSeeder
```

---

## Resetting Test Data

```bash
# Wipe everything and start fresh
php artisan migrate:fresh --seed
php artisan db:seed --class=LocalTestSeeder
```
