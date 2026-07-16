<p align="center"><a href="https://devliopay.com" target="_blank"><img src="public/logo.svg" width="120" alt="DevlioPay Logo"></a></p>

<h1 align="center">DevlioPay</h1>

<p align="center">Open-source billing platform for hosting businesses. Manage services, invoices, payments, support tickets, and server provisioning — all in one place.</p>

---

## Features

- **Store & Shopping Cart** — Sell hosting products with recurring or one-time pricing
- **Billing & Invoicing** — Auto-generate invoices, handle overdue payments, credit system
- **Payment Gateways** — Stripe & PayPal integration with webhook support
- **Server Provisioning** — Pterodactyl game panel integration (create, suspend, terminate servers)
- **Client Dashboard** — View services, invoices, tickets, domains, and account credits
- **Support Tickets** — Multi-department ticket system with admin notifications
- **Knowledge Base** — Publish help articles for your customers
- **Server Status** — Real-time status page with uptime checks and incident tracking
- **Admin Panel** — Filament-powered admin area with full CRUD management
- **Two-Factor Auth** — TOTP-based 2FA for client accounts
- **Promo Codes** — Percentage and fixed discount codes with usage limits
- **Tax Rates** — Automatic tax calculation based on customer location
- **Multi-Currency** — Support for multiple currencies with exchange rate sync
- **Domains** — Namecheap integration for domain registration and management
- **Activity Logging** — Track all changes across the system
- **Email Notifications** — Automated emails for invoices, payments, service actions, and tickets

## Tech Stack

- **Backend:** Laravel 13, PHP 8.3+
- **Admin Panel:** Filament 3.2
- **Frontend:** Tailwind CSS 4, Alpine.js, Blade
- **Database:** SQLite (default), MySQL/PostgreSQL compatible
- **Queue:** Database-driven queue for background jobs
- **Payments:** Stripe & PayPal APIs
- **Server Provisioning:** Pterodactyl API

## Requirements

- PHP 8.3+
- Composer 2
- Node.js 20+
- SQLite (or MySQL/PostgreSQL)

## Installation

```bash
# Clone the repository
git clone https://github.com/your-org/devliopay.git
cd devliopay

# Install PHP dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Install & build frontend assets
npm install
npm run build

# Run database migrations
php artisan migrate

# Seed initial data (currencies, settings, email templates, ticket departments)
php artisan db:seed

# Create your admin account
php artisan make:account

# Start the development server
composer dev
```

## Configuration

### Payment Gateways

| Variable | Description |
|----------|-------------|
| `STRIPE_SECRET_KEY` | Stripe secret key |
| `STRIPE_PUBLISHABLE_KEY` | Stripe publishable key |
| `STRIPE_WEBHOOK_SECRET` | Stripe webhook signing secret |
| `PAYPAL_CLIENT_ID` | PayPal REST API client ID |
| `PAYPAL_CLIENT_SECRET` | PayPal REST API secret |
| `PAYPAL_MODE` | `sandbox` or `live` |

### Server Provisioning

| Variable | Description |
|----------|-------------|
| `PTERODACTYL_HOST` | Pterodactyl panel URL |
| `PTERODACTYL_API_KEY` | Pterodactyl application API key |

## Console Commands

| Command | Description |
|---------|-------------|
| `php artisan make:account` | Create a new user or admin account |
| `php artisan billing:process` | Process recurring billing and overdue invoices |
| `php artisan domains:check-expiry` | Check for domains expiring soon |
| `php artisan provision:service` | Manually trigger service provisioning |

## Scheduled Tasks

Configure your server's cron to run `php artisan schedule:run` every minute:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

The scheduler handles:
- **Daily at midnight:** Recurring billing invoice generation
- **Hourly:** Overdue invoice processing and service suspension
- **Daily at 8 AM:** Domain expiry checks

## Testing

```bash
php artisan test
```

## License

DevlioPay is open-source software licensed under the MIT license.
