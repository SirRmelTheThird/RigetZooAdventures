<div align="center">

# 🦁 Riget Zoo Adventures

[![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4.svg?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1.svg?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Stripe](https://img.shields.io/badge/Stripe-Payments-635BFF.svg?style=for-the-badge&logo=stripe&logoColor=white)](https://stripe.com/)
[![Composer](https://img.shields.io/badge/Composer-Dependencies-885630.svg?style=for-the-badge&logo=composer&logoColor=white)](https://getcomposer.org/)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg?style=for-the-badge)](https://opensource.org/licenses/MIT)

</div>

## 📖 Overview

Riget Zoo Adventures is a modern PHP zoo booking and accommodation system. It's built on a custom MVC framework backed by Eloquent ORM, with real Stripe payment processing and transaction-safe order creation.

The app lets users sign up, browse tickets and accommodations, build a cart, check out with a real Stripe payment, and track order history and reward points from the profile page.

## ✨ Features

- User authentication with bcrypt password hashing
- CSRF protection and session regeneration on login
- Ticket booking (standard and premium)
- Accommodation reservations with availability checking
- Session-based shopping cart
- Stripe payments via the Payment Intent API, with webhook support
- Order management with transaction-safe order creation (rolls back on failure)
- Reward points (10 points per dollar spent)
- Custom exception handling with dedicated 404/500 error pages

## 🗂️ Project Structure

| Folder | Description |
|---|---|
| `public` | Web root — entry point and exception handling |
| `src/Controllers` | Request handlers |
| `src/Models` | Eloquent ORM models |
| `src/Services` | Business logic (Auth, Booking, Payment) |
| `src/Core` | Framework core (Router, Session, CSRF, etc.) |
| `src/Exceptions` | Custom exception classes |
| `src/Views` | HTML templates, including `errors/` |
| `database/migrations` | Eloquent migrations |
| `database/seeders` | Sample data |
| `config` | Configuration |
| `storage/logs` | Application logs |

## 🛠️ Technologies Used

- PHP 7.4+
- Eloquent ORM (`illuminate/database`)
- MySQL / MariaDB
- Stripe PHP SDK (`stripe/stripe-php`)
- Environment config (`vlucas/phpdotenv`)
- Bootstrap 5 UI
- Custom regex-based MVC router

## ✅ Requirements

Before running the application, make sure you have these installed:

- PHP 7.4 or higher
- MySQL/MariaDB
- Composer
- A Stripe account (for payments)

## 📥 Installation

Install dependencies:

```bash
composer install
```

This installs Eloquent ORM, environment config, and the Stripe PHP SDK.

Copy the environment file:

```bash
cp .env.example .env
```

Edit `.env` with your settings:

```env
# Database Configuration
DB_HOST=localhost
DB_NAME=riget_zoo_adventures
DB_USER=root
DB_PASSWORD=your_password_here

# Stripe Configuration (Get from https://dashboard.stripe.com/test/apikeys)
STRIPE_SECRET_KEY=sk_test_51...
STRIPE_PUBLISHABLE_KEY=pk_test_51...
STRIPE_WEBHOOK_SECRET=whsec_...  # From Stripe webhook settings

# Application Configuration
APP_ENV=development
APP_DEBUG=true
```

Create the database:

```bash
mysql -u root -p
```

```sql
CREATE DATABASE riget_zoo_adventures;
EXIT;
```

Run migrations and seeders:

```bash
php cli migrate:fresh --seed
```

This creates all tables (customers, orders, order_items, tickets, accommodations, reward_points) and inserts sample data (3 test users, tickets, accommodations).

## 🚀 Running the Project

Pick whichever start method is easiest:

| Option | Command | Notes |
|---|---|---|
| Batch File (Windows) | `start-server.bat` | Just double-click the file |
| PowerShell (Windows) | `./start-server.ps1` | Run from a PowerShell terminal |
| Manual | `php -S localhost:8000 -t public` | Works on any platform |

Visit: **http://localhost:8000**

Keep the terminal window open while the server runs, and press `Ctrl+C` to stop it.

### Test Accounts (after seeding)

- `john@example.com` / `password123`
- `jane@example.com` / `password123`
- `admin@riget.com` / `admin123`

### Stripe Test Cards

- **Success**: 4242 4242 4242 4242 (any future date, any CVC)
- **Decline**: 4000 0000 0000 0002
- **Requires Auth**: 4000 0025 0000 3155

## 📜 Development Commands

| Command | Description |
|---|---|
| `php cli migrate` | Run migrations only |
| `php cli migrate:fresh` | Reset and re-run migrations |
| `php cli migrate:fresh --seed` | Reset database, migrate, and seed |
| `php cli db:seed` | Seed database only |
| `php -S localhost:8000 -t public` | Start the development server |
| `composer dump-autoload` | Regenerate the autoloader |
| `stripe listen --forward-to localhost:8000/stripe/webhook` | Forward Stripe events for local webhook testing |

## 🧪 Testing

Manual testing checklist, end to end:

1. **Sign Up** → Create new account
2. **Browse** → View tickets and accommodations
3. **Add to Cart** → Select dates and quantities
4. **Checkout** → Login if not authenticated
5. **Payment** → Enter test card details
6. **Confirmation** → Order created, points awarded
7. **Profile** → View order history

Covers:

- **Authentication** — sign up, log in, session regenerates on login, log out, invalid credentials rejected
- **Shopping Cart** — add standard/premium tickets, add accommodation, correct totals, remove items
- **Payment Flow** — checkout creates a Payment Intent, test card succeeds, order stores `stripe_payment_id`, declined card fails gracefully without creating an order
- **Availability** — cannot double-book accommodation on occupied or overlapping dates
- **Error Handling** — invalid routes show a 404 page, server errors show a 500 page (or the exception in debug mode), all errors log to `storage/logs/app.log`
- **Transactions** — a failed order-item creation rolls back the whole order; database stays consistent on errors

## 🔄 Resetting the Database

If data gets into a bad state or before re-testing:

```bash
php cli migrate:fresh --seed
```

## 🔗 Stripe Webhook Setup (optional for development)

1. Go to https://dashboard.stripe.com/webhooks
2. Click "Add endpoint"
3. URL: `https://yourdomain.com/stripe/webhook`
4. Events: `payment_intent.succeeded`, `payment_intent.payment_failed`
5. Copy the webhook secret into `.env`

For local testing, use the Stripe CLI:

```bash
stripe listen --forward-to localhost:8000/stripe/webhook
```

## 🖼️ About Images

The app works without any images — it falls back to placeholders automatically. To add your own, drop them into `public/assets/images/`. Needed filenames: `logo.png`, `deer.jpg` (hero background), `hotel.jpg`, `train.jpg`, `ticket.jpg`, `giraffe.jpg`, `seal.jpg`, `giftshop.jpg`, `res.jpg`. Images are optional and can be added anytime.

## 🧯 Common Problems

### "php is not recognized"

Install PHP from: https://www.php.net/downloads.php.

```bash
# 1. Wait for the installation to finish
# 2. Close and reopen your terminal/VS Code
# 3. Try again
```

### "Composer not found"

Install Composer from: https://getcomposer.org/download/

### "Class 'Illuminate\Database\Capsule\Manager' not found"

```bash
composer install
```

### "Access denied for user 'root'@'localhost'"

Update your DB credentials in `.env`.

### "Table 'customers' doesn't exist"

```bash
php cli migrate:fresh
```

### "Stripe API key not configured"

Add your Stripe keys to `.env`.

### Payment Fails Silently

1. Check `storage/logs/app.log`
2. Verify your Stripe test keys are correct
3. Ensure `APP_DEBUG=true` in `.env` for detailed errors

### Database Errors (general)

Make sure MySQL is running and your `.env` credentials are correct, then:

```bash
php cli migrate:fresh --seed
```

### View Detailed Errors

Set `APP_DEBUG=true` in `.env`.

## 🚦 Production Deployment

Before deploying to production:

1. Set `APP_DEBUG=false` in `.env`
2. Use real Stripe keys (not test keys)
3. Configure the Stripe webhook: `https://yourdomain.com/stripe/webhook`
4. Enable HTTPS (required by Stripe)
5. Set a strong database password
6. Configure proper error logging
7. Set up database backups
8. Test the payment flow end-to-end
