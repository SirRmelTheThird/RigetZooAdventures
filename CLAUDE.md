# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Riget Zoo Adventures is a modern PHP web application for booking zoo tickets and accommodations. It uses **Eloquent ORM** for database operations with a custom MVC framework, following modern PHP practices with PSR-4 autoloading and real Stripe payment processing.

## Development Commands

### Database Operations
```bash
# Run migrations (create/update tables using Eloquent schema)
php cli migrate

# Reset database and run migrations
php cli migrate:fresh

# Reset database, run migrations, and seed sample data
php cli migrate:fresh --seed

# Seed database with sample data only
php cli db:seed
```

### Running the Application
```bash
# Start PHP built-in server (preferred for development)
php -S localhost:8000 -t public

# Alternative: use XAMPP/WAMP and point DocumentRoot to public/ directory
```

### Dependency Management
```bash
# Install dependencies (Eloquent ORM, PHPDotenv, Stripe)
composer install

# Regenerate autoloader after adding new classes
composer dump-autoload
```

## Architecture

### Eloquent ORM Models
All models extend `Illuminate\Database\Eloquent\Model`:

**Models** (`src/Models/`):
- `Customer.php` - User accounts with automatic password hashing
- `Order.php` - Customer orders with status tracking
- `OrderItem.php` - Individual order line items
- `Ticket.php` - Ticket types and pricing
- `Accommodation.php` - Lodging options with availability checking
- `RewardPoint.php` - Customer loyalty points

**Key Features:**
- Relationships defined (hasMany, belongsTo)
- Query scopes for filtering
- Automatic timestamps
- Type casting for decimals/dates

### Database Schema
Uses snake_case for column names:
- `customers` table (not `users`)
- `customer_id`, `first_name`, `created_at`, etc.
- All migrations in `database/migrations/`

### Controllers
All controllers extend `Controllers\Controller` base class. Controller filenames do NOT include "Controller" suffix.

**Request Flow:**
1. All requests hit `public/index.php`
2. Autoloader registers classes from `src/` and `config/`
3. `config/bootstrap.php` loads:
   - Environment variables via PHPDotenv
   - Session management
   - Eloquent ORM connection
   - Event listeners
   - Route definitions
4. Router matches URI to controller action
5. Middleware runs (CSRF, Auth)
6. Controller executes and returns response
7. Global exception handler catches errors

### Core Components

**Router** (`src/Core/Router.php`)
- Custom routing with regex pattern matching
- Middleware support per-route
- Controller format: `ControllerName@methodName`
- Throws `NotFoundException` for invalid routes

**Request/Response** (`src/Core/Request.php`, `src/Core/Response.php`)
- Request: wraps PHP superglobals with sanitization
- Response: handles redirects, JSON, and view rendering
- Views use `extract()` to pass variables to templates

**Session** (`src/Core/Session.php`)
- Secure session management with httpOnly and sameSite flags
- ID regeneration on login for security
- Cart stored in session as `$_SESSION['cart']`
- Uses `customer_id` and `username` keys

**CSRF Protection** (`src/Core/CSRF.php`, `src/Middleware/CSRFMiddleware.php`)
- Token generated per session
- All POST routes must include CSRFMiddleware
- Forms use `<?php echo \Core\CSRF::field(); ?>` to include token

**Event System** (`src/Core/EventDispatcher.php`)
- Simple observer pattern
- Events: `Events\OrderCreated`
- Listeners: `Listeners\LogOrderCreation`
- Register defaults in `EventDispatcher::registerDefaults()`

**Logging** (`src/Core/Logger.php`)
- Writes to `storage/logs/app.log`
- Methods: `info()`, `error()`, `exception()`

**Exception Handling**
- Custom exceptions in `src/Exceptions/`
- `NotFoundException` (404)
- `ValidationException` (422)
- `PaymentException` (402)
- Global handler in `public/index.php`
- Error views in `src/Views/errors/`

### Services Layer

**AuthService** (`src/Services/AuthService.php`)
- Uses Eloquent Customer model
- Session regeneration on login
- Password verification via model method

**BookingService** (`src/Services/BookingService.php`)
- **Transaction support via Eloquent**
- Creates orders with automatic rollback on failure
- Awards reward points
- Dispatches `OrderCreated` event

**PaymentService** (`src/Services/PaymentService.php`)
- Real Stripe integration
- Creates Payment Intents
- Verifies payment completion
- Handles webhooks
- Cancels payments

### Cart Structure
Cart stored in session with structure:
```php
$_SESSION['cart'] = [
    'items' => [
        ['type' => 'ticket', 'adult' => 2, 'child' => 1, 'total' => 130, 'date' => '2024-01-15'],
        ['type' => 'accommodation', 'id' => 1, 'name' => 'Safari Lodge', 'total' => 400, 'startDate' => '2024-01-15', 'endDate' => '2024-01-17']
    ],
    'total' => 530
]
```

### Payment Flow

1. **Checkout Page** - Creates Stripe Payment Intent
2. **Client Side** - Stripe Elements for card input
3. **Payment Processing** - Verifies payment with Stripe
4. **Order Creation** - Only after successful payment
5. **Webhook** - Stripe notifies for payment events

**Environment Variables Required:**
```
STRIPE_SECRET_KEY=sk_test_...
STRIPE_PUBLISHABLE_KEY=pk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
```

## Key Files

### Entry Points
- `public/index.php` - Front controller with routing and exception handling
- `config/bootstrap.php` - Eloquent ORM configuration and app initialization
- `cli` - Command-line tool for migrations and seeding

### Configuration
- `.env` - Environment variables (DB credentials, Stripe keys, debug mode)
- `config/Config.php` - Loads environment variables
- `composer.json` - Dependencies (Eloquent, Stripe, PHPDotenv)

### Views
- `src/Views/layouts/header.php` - Shared header (Bootstrap 5)
- `src/Views/layouts/footer.php` - Shared footer
- `src/Views/errors/` - Error pages (404, 500)
- Views loaded via `Response::view('view_name', ['data' => $value])`

## Common Patterns

### Adding a New Route
Edit `public/index.php`:
```php
$router->post('/new-route', 'NewCtrl@method', ['CSRFMiddleware', 'AuthMiddleware']);
```

### Creating a Controller
```php
namespace Controllers;
use Core\Request;
use Core\Response;

class NewCtrl extends Controller {
    public function method() {
        $data = Request::post('field_name');
        Response::view('view_name', ['data' => $data]);
    }
}
```
File: `src/Controllers/NewCtrl.php`

### Creating an Eloquent Model
```php
namespace Models;
use Illuminate\Database\Eloquent\Model;

class NewModel extends Model {
    protected $table = 'table_name';
    
    protected $fillable = ['field1', 'field2'];
    
    // Relationships
    public function related() {
        return $this->belongsTo(Related::class);
    }
}
```
File: `src/Models/NewModel.php`

### Using Transactions
```php
use Illuminate\Database\Capsule\Manager as DB;

DB::transaction(function() {
    // All database operations here
    // Automatic rollback on exception
});
```

## Testing Approach

No automated test framework is configured. Manual testing checklist:
1. Sign up new user
2. Login with credentials
3. Add tickets to cart (standard and premium)
4. Add accommodation to cart
5. View cart and verify totals
6. Checkout (requires authentication)
7. Complete payment with Stripe test card (4242 4242 4242 4242)
8. Verify order created with payment ID
9. View order history in profile
10. Verify reward points awarded

Test credentials after seeding:
- `john@example.com` / `password123`
- `jane@example.com` / `password123`
- `admin@riget.com` / `admin123`

**Stripe Test Cards:**
- Success: 4242 4242 4242 4242
- Decline: 4000 0000 0000 0002
- Requires Auth: 4000 0025 0000 3155

## Security Notes

- All passwords hashed with bcrypt (cost 12)
- All database queries use Eloquent ORM (no SQL injection)
- CSRF tokens required on all POST requests
- Session IDs regenerated on authentication state changes
- Stripe API key must be in `.env`, never committed to git
- User input sanitized via `htmlspecialchars()` in views
- Payment verified before order creation
- Transaction support prevents partial orders

## Known Improvements Made

✅ **Migrated to Eloquent ORM** - No PDO code remains
✅ **Transaction support** - Order creation wrapped in DB transaction
✅ **Real Stripe payments** - Payment Intent integration with verification
✅ **Exception handling** - No die() calls, proper error pages
✅ **Session security** - Regenerates on login
✅ **Availability checking** - Accommodations check for overlapping bookings
✅ **Consistent database** - All tables use snake_case, customers table
✅ **Proper relationships** - Models define Eloquent relationships

## Repository State

The codebase has been modernized with:
- Full Eloquent ORM migration
- Real Stripe payment processing
- Transaction support for data integrity
- Custom exception handling
- Improved security practices

## Development Notes

- Use Eloquent models for all database operations
- Wrap multi-step operations in `DB::transaction()`
- Throw custom exceptions instead of die() or exit()
- Verify payments before creating orders
- Test with Stripe test mode keys
- Check `.env.example` for required configuration
