#!/bin/bash

# DevlioPay - Automatic Installation Script for Ubuntu
# Run as root: sudo bash install.sh

set -e

# ─── Colors ────────────────────────────────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

print_banner() {
    echo -e "${CYAN}"
    echo "██████╗ ███████╗██╗   ██╗██╗     ██╗ ██████╗ ██████╗  █████╗ ██╗   ██╗"
    echo "██╔══██╗██╔════╝██║   ██║██║     ██║██╔═══██╗██╔══██╗██╔══██╗╚██╗ ██╔╝"
    echo "██║  ██║█████╗  ██║   ██║██║     ██║██║   ██║██████╔╝███████║ ╚████╔╝ "
    echo "██║  ██║██╔══╝  ╚██╗ ██╔╝██║     ██║██║   ██║██╔═══╝ ██╔══██║  ╚██╔╝  "
    echo "██████╔╝███████╗ ╚████╔╝ ███████╗██║╚██████╔╝██║     ██║  ██║   ██║   "
    echo "╚═════╝ ╚══════╝  ╚═══╝  ╚══════╝╚═╝ ╚═════╝ ╚═╝     ╚═╝  ╚═╝   ╚═╝   "
    echo -e "${NC}"
    echo -e "  ${CYAN}Automatic Installation Script for Ubuntu${NC}"
    echo ""
}

# ─── Configuration ─────────────────────────────────────────────────────────────
DOMAIN="${1:-}"

# If no argument passed, prompt the user
if [ -z "$DOMAIN" ]; then
    echo -e "${CYAN}Enter your domain name or server IP (e.g. devliopay.com or 123.45.67.89):${NC}"
    read -r DOMAIN
fi

# Default to localhost if still empty
DOMAIN="${DOMAIN:-localhost}"

# Detect if input is an IP address or a domain
IS_IP=false
if echo "$DOMAIN" | grep -qE '^([0-9]{1,3}\.){3}[0-9]{1,3}$'; then
    IS_IP=true
fi

INSTALL_DIR="/var/www/devliopay"
ADMIN_NAME="Admin"
ADMIN_EMAIL="admin@${DOMAIN}"
ADMIN_PASSWORD=$(openssl rand -base64 12)
DB_PASSWORD=$(openssl rand -base64 32)

print_banner

echo -e "${YELLOW}[1/12] System Update & Dependencies${NC}"
apt-get update -y
apt-get upgrade -y
apt-get install -y software-properties-common curl wget git unzip zip nginx sqlite3 certbot python3-certbot-nginx

echo -e "${YELLOW}[2/12] Installing PHP 8.3+ & Extensions${NC}"
add-apt-repository -y ppa:ondrej/php
apt-get update -y
apt-get install -y php8.3 php8.3-cli php8.3-common php8.3-curl php8.3-mbstring php8.3-xml php8.3-zip php8.3-bcmath php8.3-gd php8.3-sqlite3 php8.3-intl php8.3-tokenizer php8.3-dom php8.3-fileinfo php8.3-redis php8.3-fpm

echo -e "${YELLOW}[3/12] Installing Node.js 22.x & npm${NC}"
curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
apt-get install -y nodejs

echo -e "${YELLOW}[4/12] Installing Composer${NC}"
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

echo -e "${YELLOW}[5/12] Setting up Web Server User${NC}"
if ! id -g devliopay >/dev/null 2>&1; then
    addgroup --system devliopay
fi
if ! id -u devliopay >/dev/null 2>&1; then
    adduser --system --ingroup devliopay --home $INSTALL_DIR --shell /bin/bash devliopay
fi

echo -e "${YELLOW}[6/12] Cloning Repository${NC}"
if [ -d "$INSTALL_DIR" ]; then
    rm -rf "$INSTALL_DIR"
fi
git clone https://github.com/Bebonaiem/devliopay.git "$INSTALL_DIR"
cd "$INSTALL_DIR"

# Set ownership before composer/npm so they can write files
chown -R devliopay:devliopay "$INSTALL_DIR"
git config --global --add safe.directory "$INSTALL_DIR"

echo -e "${YELLOW}[7/12] Installing PHP Dependencies${NC}"
sudo -u devliopay composer install --no-dev --optimize-autoloader --no-interaction

echo -e "${YELLOW}[8/12] Installing Node Dependencies & Building Assets${NC}"
sudo -u devliopay npm install
sudo -u devliopay npm run build

echo -e "${YELLOW}[9/12] Environment Configuration${NC}"
cp .env.example .env

# Generate app key
php artisan key:generate --force --no-interaction

# Configure database
touch database/database.sqlite
chmod 664 database/database.sqlite
chown devliopay:devliopay database/database.sqlite

# Update .env
sed -i "s|APP_URL=http://localhost|APP_URL=${APP_URL}|g" .env
sed -i "s|APP_ENV=local|APP_ENV=production|g" .env
sed -i "s|APP_DEBUG=true|APP_DEBUG=false|g" .env
sed -i "s|MAIL_MAILER=log|MAIL_MAILER=smtp|g" .env

echo -e "${YELLOW}[10/12] Database Migration & Seeding${NC}"
php artisan migrate --force --no-interaction
php artisan db:seed --force --no-interaction

echo -e "${YELLOW}[11/12] Creating Admin User${NC}"
php artisan tinker --execute="
\$user = App\Models\User::create([
    'name' => '${ADMIN_NAME}',
    'email' => '${ADMIN_EMAIL}',
    'password' => bcrypt('${ADMIN_PASSWORD}'),
    'email_verified_at' => now(),
    'is_admin' => true,
]);
echo 'Admin user created: ' . \$user->email;
" 2>&1

echo -e "${YELLOW}[12/12] Permissions & Cache${NC}"
chown -R devliopay:devliopay "$INSTALL_DIR"
chmod -R 755 "$INSTALL_DIR"
chmod -R 775 "$INSTALL_DIR/storage" 2>/dev/null || true
chmod -R 775 "$INSTALL_DIR/bootstrap/cache" 2>/dev/null || true

# Create storage link
php artisan storage:link --force --no-interaction

# Clear and cache
php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction
php artisan icon:cache --no-interaction 2>/dev/null || true

# ─── Nginx Configuration ──────────────────────────────────────────────────────
echo -e "${YELLOW}Setting up Nginx...${NC}"

# Detect PHP-FPM socket
PHP_FPM_SOCK=$(find /var/run/php -name "php*-fpm.sock" 2>/dev/null | head -1)
if [ -z "$PHP_FPM_SOCK" ]; then
    # Ensure php-fpm is installed and started
    apt-get install -y php8.3-fpm 2>/dev/null || true
    systemctl start php8.3-fpm 2>/dev/null || true
    PHP_FPM_SOCK=$(find /var/run/php -name "php*-fpm.sock" 2>/dev/null | head -1)
fi
if [ -z "$PHP_FPM_SOCK" ]; then
    PHP_FPM_SOCK="/var/run/php/php8.3-fpm.sock"
fi
echo -e "${CYAN}Using PHP-FPM socket: ${PHP_FPM_SOCK}${NC}"

# For IPs, use _ to match all requests; for domains use the actual domain
if [ "$IS_IP" = true ]; then
    NGINX_SERVER_NAME="_"
    APP_URL="http://${DOMAIN}"
else
    NGINX_SERVER_NAME="${DOMAIN}"
    APP_URL="http://${DOMAIN}"
fi

cat > /etc/nginx/sites-available/devliopay <<NGINX
server {
    listen 80;
    listen [::]:80;
    server_name ${NGINX_SERVER_NAME};
    root ${INSTALL_DIR}/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php\$ {
        fastcgi_pass unix:${PHP_FPM_SOCK};
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    client_max_body_size 64M;
}
NGINX

ln -sf /etc/nginx/sites-available/devliopay /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default 2>/dev/null || true

# Restart services
PHP_FPM_VERSION=$(echo "$PHP_FPM_SOCK" | sed -E 's|.*php([0-9.]+)-fpm.*|\1|')
systemctl restart "php${PHP_FPM_VERSION}-fpm" 2>/dev/null || systemctl restart php8.3-fpm 2>/dev/null || true
systemctl restart nginx

# ─── Setup Queue Worker (systemd) ─────────────────────────────────────────────
cat > /etc/systemd/system/devliopay-worker.service <<SYSTEMD
[Unit]
Description=DevlioPay Queue Worker
After=network.target

[Service]
User=devliopay
Group=devliopay
Restart=always
ExecStart=/usr/bin/php ${INSTALL_DIR}/artisan queue:work --sleep=3 --tries=3 --max-time=3600
RestartSec=10
StopTimeout=60

[Install]
WantedBy=multi-user.target
SYSTEMD

systemctl daemon-reload
systemctl enable devliopay-worker
systemctl start devliopay-worker

# ─── Setup Scheduler (cron) ──────────────────────────────────────────────────
CRON_LINE="* * * * * cd ${INSTALL_DIR} && php artisan schedule:run >> /dev/null 2>&1"
(crontab -l 2>/dev/null | grep -v "artisan schedule:run" ; echo "$CRON_LINE") | crontab -

# ─── SSL Certificate (optional) ──────────────────────────────────────────────
if [ "$IS_IP" = false ] && [ "$DOMAIN" != "localhost" ]; then
    echo -e "${YELLOW}Setting up SSL certificate...${NC}"
    certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos --email "admin@${DOMAIN}" || true
fi

echo ""
echo -e "${GREEN}============================================================${NC}"
echo -e "${GREEN}  DevlioPay Installation Complete!${NC}"
echo -e "${GREEN}============================================================${NC}"
echo ""
echo -e "  ${CYAN}URL:${NC}       ${APP_URL}"
echo -e "  ${CYAN}Admin:${NC}     ${ADMIN_EMAIL}"
echo -e "  ${CYAN}Password:${NC}  ${ADMIN_PASSWORD}"
echo ""
echo -e "  ${YELLOW}⚠  Save these credentials! They won't be shown again.${NC}"
echo -e "  ${YELLOW}⚠  Edit .env at ${INSTALL_DIR}/.env to configure${NC}"
echo -e "  ${YELLOW}   Stripe, PayPal, Pterodactyl, and Mail settings.${NC}"
echo ""
echo -e "  ${CYAN}Useful commands:${NC}"
echo -e "  cd ${INSTALL_DIR}"
echo -e "  php artisan tinker          # Access app shell"
echo -e "  php artisan queue:work      # Process jobs manually"
echo -e "  php artisan migrate:status  # Check migration status"
echo ""
