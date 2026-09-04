# Riget Zoo Adventures 🦁

A modern PHP zoo booking and accommodation system with **Eloquent ORM**, **real Stripe payment processing**, and **transaction safety**.

## ✨ Features

- 🔐 **User Authentication** - Secure signup/login with bcrypt hashing
- 🎟️ **Ticket Booking** - Standard and premium zoo tickets
- 🏠 **Accommodation Reservations** - Safari lodges with real availability checking
- 🛒 **Shopping Cart** - Session-based cart management
- 💳 **Stripe Payments** - Real payment processing with Payment Intents
- 📦 **Order Management** - Complete order history with transaction safety
- ⭐ **Reward Points** - 10 points per dollar spent
- 🔄 **Transaction Safety** - ACID-compliant order creation
- 🚨 **Error Handling** - Custom exceptions and beautiful error pages

## 🏗️ Architecture

- **Framework**: Custom MVC with PSR-4 autoloading
- **Database**: Eloquent ORM (Laravel's database layer)
- **Payments**: Stripe Payment Intent API
- **Authentication**: Secure session-based with bcrypt
- **Frontend**: Bootstrap 5 + PHP templates
- **Exception Handling**: Custom exceptions with global handler

## 🚀 Quick Start

### Prerequisites

- PHP 7.4+
- MySQL/MariaDB
- Composer
- Stripe account (test mode)

### Installation

```bash
# 1. Install dependencies
composer install

# 2. Configure environment
cp .env.example .env
# Edit .env with your database and Stripe credentials

# 3. Create database
mysql -u root -p -e "CREATE DATABASE riget_zoo_adventures;"

# 4. Run migrations and seed data
php cli migrate:fresh --seed

# 5. Start development server
php -S localhost:8000 -t public
```

Visit: **http://localhost:8000**

### Test Accounts (after seeding)

- `john@example.com` / `password123`
- `jane@example.com` / `password123`
- `admin@riget.com` / `admin123`

### Stripe Test Cards

- **Success**: 4242 4242 4242 4242 (any future date, any CVC)
- **Decline**: 4000 0000 0000 0002
- **Requires Auth**: 4000 0025 0000 3155

## 📚 Documentation

- **[SETUP_GUIDE.md](SETUP_GUIDE.md)** - Detailed installation instructions
- **[CLAUDE.md](CLAUDE.md)** - Architecture and development guide
- **[MODERNIZATION_SUMMARY.md](MODERNIZATION_SUMMARY.md)** - Complete changelog

## 🛠️ Tech Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL with Eloquent ORM
- **Payments**: Stripe PHP SDK
- **Frontend**: HTML5, Bootstrap 5, Vanilla JS
- **Routing**: Custom regex-based router
- **Session**: PHP sessions with security hardening
- **Logging**: File-based application logging

## 📁 Project Structure

```
├── public/              # Web root
│   └── index.php       # Entry point + exception handling
├── src/
│   ├── Controllers/    # Request handlers
│   ├── Models/         # Eloquent ORM models
│   ├── Services/       # Business logic (Auth, Booking, Payment)
│   ├── Core/           # Framework (Router, Session, CSRF, etc.)
│   ├── Exceptions/     # Custom exceptions
│   └── Views/          # HTML templates
├── database/
│   ├── migrations/     # Eloquent migrations
│   └── seeders/        # Sample data
├── config/             # Configuration
└── storage/logs/       # Application logs
```

## 🔒 Security Features

- ✅ Bcrypt password hashing (cost 12)
- ✅ CSRF protection on all POST requests
- ✅ Session regeneration on login
- ✅ SQL injection protection (Eloquent ORM)
- ✅ XSS protection via htmlspecialchars()
- ✅ Payment verification before order creation
- ✅ Secure session cookies (httpOnly, sameSite)
- ✅ Exception handling (no exposed stack traces)

## 💡 Recent Improvements (2026-09-03)

### ✅ Migrated to Eloquent ORM
- Removed all PDO code
- Full Eloquent models with relationships
- Query builder and scopes
- Automatic timestamps

### ✅ Real Stripe Integration
- Removed simulated payments
- Payment Intent API
- Webhook support
- Transaction IDs stored in orders

### ✅ Transaction Support
- DB::transaction() wrapper
- Automatic rollback on failure
- ACID compliance
- No more partial orders

### ✅ Exception Handling
- Removed all die() calls
- Custom exception classes (NotFoundException, ValidationException, PaymentException)
- Global exception handler in front controller
- Beautiful error pages (404, 500)

### ✅ Security Enhancements
- Session regeneration on login (prevents session fixation)
- Real availability checking for accommodations
- Payment verification before order creation
- Consistent snake_case database schema

## 📋 API Routes

| Method | Route | Description | Middleware |
|--------|-------|-------------|------------|
| GET | `/` | Home page | - |
| GET | `/login` | Login form | - |
| POST | `/login` | Process login | CSRF |
| GET | `/signup` | Signup form | - |
| POST | `/signup` | Process signup | CSRF |
| GET | `/logout` | Logout | Auth |
| GET | `/tickets` | Ticket selection | - |
| POST | `/tickets/standard` | Add standard to cart | CSRF |
| POST | `/tickets/premium` | Add premium to cart | CSRF |
| GET | `/accommodations` | Accommodations list | - |
| POST | `/accommodations/add` | Add to cart | CSRF |
| GET | `/cart` | View cart | - |
| POST | `/cart/remove` | Remove item | CSRF, Auth |
| GET | `/checkout` | Checkout page | Auth |
| POST | `/payment/process` | Process payment | CSRF, Auth |
| POST | `/stripe/webhook` | Stripe webhook | - |
| GET | `/profile` | User profile | Auth |

## 🧪 Testing

### Manual Testing Checklist

```bash
# Authentication
✓ Sign up new user
✓ Login with credentials
✓ Session regenerates on login
✓ Logout

# Shopping
✓ Add standard tickets to cart
✓ Add premium tickets to cart
✓ Add accommodation to cart
✓ View cart with correct totals
✓ Remove items from cart

# Payment
✓ Checkout requires login
✓ Stripe Payment Intent created
✓ Card form displays
✓ Test card succeeds
✓ Order created with payment ID
✓ Reward points awarded
✓ Failed payment doesn't create order

# Availability
✓ Book accommodation for dates
✓ Overlapping booking rejected
✓ Available dates succeed

# Error Handling
✓ Invalid route shows 404 page
✓ Server errors show 500 page
✓ All errors logged to storage/logs/app.log
```

## 📝 Development Commands

```bash
# Database
php cli migrate              # Run migrations
php cli migrate:fresh        # Reset and migrate
php cli migrate:fresh --seed # Reset, migrate, and seed
php cli db:seed              # Seed only

# Development
php -S localhost:8000 -t public   # Start dev server
composer dump-autoload            # Regenerate autoloader

# Stripe Webhooks (local testing)
stripe listen --forward-to localhost:8000/stripe/webhook
```

## 🐛 Troubleshooting

### Database Connection Errors
```bash
php cli migrate:fresh --seed
```

### Stripe Not Working
- Check `.env` has valid test keys from https://dashboard.stripe.com/test/apikeys
- Verify STRIPE_SECRET_KEY starts with `sk_test_`
- Check storage/logs/app.log for errors

### Composer Errors
```bash
composer install
composer dump-autoload
```

### View Detailed Errors
Set `APP_DEBUG=true` in `.env`

### Check Application Logs
```bash
tail -f storage/logs/app.log
```

## 🚦 Production Deployment

Before deploying to production:

1. Set `APP_DEBUG=false` in `.env`
2. Use real Stripe keys (not test keys)
3. Configure Stripe webhook: `https://yourdomain.com/stripe/webhook`
4. Enable HTTPS (required for Stripe)
5. Set strong database password
6. Configure proper error logging
7. Set up database backups
8. Test payment flow end-to-end

## 📄 License

This project is for educational purposes.

## 👥 Contributors

- Modernized and secured by Claude (Anthropic AI)
- Original project by SirRmelTheThird

## 🔮 Future Enhancements

- [ ] Email notifications for bookings
- [ ] Password reset functionality
- [ ] Admin panel for content management
- [ ] Photo gallery for accommodations
- [ ] Customer reviews and ratings
- [ ] Booking cancellation system
- [ ] Multi-language support
- [ ] API endpoints for mobile app

---

**Built with ❤️ for zoo lovers everywhere** 🦒🐘🦓

**Status**: Production Ready ✅ | **Last Updated**: 2026-09-03