<p align="center"><a href="https://github.com/Bebonaiem/devliopay" target="_blank"><img src="public/logo.svg" width="120" alt="DevlioPay Logo"></a></p>

<h1 align="center">DevlioPay</h1>

<p align="center">Open-source billing platform for hosting businesses. Manage services, invoices, payments, support tickets, and server provisioning — all in one place.</p>

<p align="center">
<a href="https://github.com/Bebonaiem/devliopay/blob/master/LICENSE"><img src="https://img.shields.io/github/license/Bebonaiem/devliopay" alt="License"></a>
<a href="https://github.com/Bebonaiem/devliopay"><img src="https://img.shields.io/github/stars/Bebonaiem/devliopay?style=social" alt="Stars"></a>
</p>

---

## Features

- **Store & Shopping Cart** — Sell hosting products with recurring or one-time pricing
- **Billing & Invoicing** — Auto-generate invoices, handle overdue payments, credit system
- **Payment Gateways** — Stripe & PayPal integration with popup-based checkout
- **Server Provisioning** — Pterodactyl game panel integration (create, suspend, terminate servers)
- **Client Dashboard** — View services, invoices, tickets, and account credits
- **Support Tickets** — Multi-department ticket system with admin notifications
- **Knowledge Base** — Publish help articles for your customers
- **Admin Panel** — Filament-powered admin with dashboard, revenue charts, reports, and full CRUD
- **Two-Factor Auth** — TOTP-based 2FA for client accounts
- **Promo Codes** — Percentage and fixed discount codes with usage limits
- **Tax Rates** — Location-based automatic tax calculation (ZIP > State > Country priority)
- **Multi-Currency** — Multiple currencies with exchange rate sync
- **15 HTML Email Templates** — Dark mode designed emails with `{variable}` placeholder system
- **Beautiful Error Pages** — Custom 401, 403, 404, 419, 429, 500, 503 pages
- **Activity Logging** — Track all changes across the system
- **Auto-Install Script** — One-command Ubuntu VPS setup with SSL

## Tech Stack

- **Backend:** Laravel 13, PHP 8.4+
- **Admin Panel:** Filament 3.2
- **Frontend:** Tailwind CSS 4, Alpine.js, Blade
- **Database:** SQLite (default), MySQL/PostgreSQL compatible
- **Queue:** Database-driven queue for background jobs
- **Payments:** Stripe & PayPal APIs
- **Server Provisioning:** Pterodactyl API

## Requirements

- PHP 8.4+
- Composer 2
- Node.js 22+
- SQLite (or MySQL/PostgreSQL)

## Quick Install (Ubuntu VPS)

```bash
# Download and run the auto-installer
curl -sL https://raw.githubusercontent.com/Bebonaiem/devliopay/master/install.sh | sudo bash
```

This will automatically install PHP 8.4, Node.js 22, Composer, Nginx, SQLite, configure everything, set up SSL with Let's Encrypt, and print your admin credentials.

## Manual Installation

```bash
# Clone the repository
git clone https://github.com/Bebonaiem/devliopay.git
cd devliopay

# Install PHP dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Install & build frontend assets
npm install
npm run build

# Run database migrations and seed initial data
php artisan migrate --seed

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
| `php artisan provision:service` | Manually trigger service provisioning |
| `php artisan sync:servers` | Sync servers from Pterodactyl |

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

DevlioPay is open-source software licensed under the [MIT license](https://github.com/Bebonaiem/devliopay/blob/master/LICENSE).
