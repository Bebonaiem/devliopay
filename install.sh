#!/bin/bash

# DevlioPay - Automatic Installation Script for Ubuntu
# Run as root: sudo bash install.sh

set -e

# ─── Colors ────────────────────────────────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
BOLD='\033[1m'
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
    echo -e "  ${CYAN}${BOLD}Automatic Installation Script for Ubuntu${NC}"
    echo ""
}

print_step() {
    echo ""
    echo -e "${YELLOW}${BOLD}[$1/$2] $3${NC}"
}

print_ok() {
    echo -e "  ${GREEN}✓${NC} $1"
}

print_error() {
    echo -e "  ${RED}✗${NC} $1"
}

print_info() {
    echo -e "  ${CYAN}→${NC} $1"
}

# ─── Ensure interactive terminal ──────────────────────────────────────────────
if [ ! -t 0 ]; then
    echo -e "${RED}Error: Cannot run interactively via pipe.${NC}"
    echo ""
    echo -e "  ${CYAN}Run these two commands instead:${NC}"
    echo ""
    echo -e "  curl -sL https://raw.githubusercontent.com/Bebonaiem/devliopay/master/install.sh -o /tmp/install-devliopay.sh"
    echo -e "  sudo bash /tmp/install-devliopay.sh"
    echo ""
    exit 1
fi

# ─── Interactive Setup Menu ────────────────────────────────────────────────────
setup_config() {
    echo ""
    echo -e "${BOLD}══════════════════════════════════════════════════════════════${NC}"
    echo -e "${BOLD}  ${CYAN}Installation Type${NC}"
    echo -e "${BOLD}══════════════════════════════════════════════════════════════${NC}"
    echo ""
    echo -e "  ${CYAN}1)${NC} Use Server IP address"
    echo -e "  ${CYAN}2)${NC} Use Domain name"
    echo ""
    echo -e "${BOLD}══════════════════════════════════════════════════════════════${NC}"
    read -rp "  Choose [1-2]: " INSTALL_TYPE

    case "$INSTALL_TYPE" in
        1)
            echo ""
            read -rp "  Enter your server IP (e.g. 123.45.67.89): " DOMAIN
            IS_IP=true
            ;;
        2)
            echo ""
            read -rp "  Enter your domain (e.g. devliopay.com): " DOMAIN
            IS_IP=false
            ;;
        *)
            echo -e "${RED}Invalid choice. Exiting.${NC}"
            exit 1
            ;;
    esac

    DOMAIN="${DOMAIN:-localhost}"
    INSTALL_DIR="/var/www/devliopay"

    echo ""
    echo -e "${BOLD}══════════════════════════════════════════════════════════════${NC}"
    echo -e "${BOLD}  ${CYAN}Admin Account Setup${NC}"
    echo -e "${BOLD}══════════════════════════════════════════════════════════════${NC}"
    echo ""
    read -rp "  Admin name [Admin]: " ADMIN_NAME
    ADMIN_NAME="${ADMIN_NAME:-Admin}"

    read -rp "  Admin email [admin@${DOMAIN}]: " ADMIN_EMAIL
    ADMIN_EMAIL="${ADMIN_EMAIL:-admin@${DOMAIN}}"

    echo -n "  Admin password: "
    read -rs ADMIN_PASSWORD
    echo ""
    if [ -z "$ADMIN_PASSWORD" ]; then
        ADMIN_PASSWORD=$(openssl rand -base64 12)
        echo -e "  ${CYAN}→ Generated: ${ADMIN_PASSWORD}${NC}"
    fi

    echo ""
    echo -e "${BOLD}══════════════════════════════════════════════════════════════${NC}"
    echo -e "${BOLD}  ${CYAN}Installation Summary${NC}"
    echo -e "${BOLD}══════════════════════════════════════════════════════════════${NC}"
    echo ""
    echo -e "  ${CYAN}URL:${NC}      http://${DOMAIN}"
    echo -e "  ${CYAN}Admin:${NC}    ${ADMIN_EMAIL}"
    echo -e "  ${CYAN}Password:${NC} ${ADMIN_PASSWORD}"
    echo ""
    echo -e "${BOLD}══════════════════════════════════════════════════════════════${NC}"
    echo ""
    read -rp "  Proceed with installation? [Y/n]: " CONFIRM
    if [[ "$CONFIRM" =~ ^[nN]$ ]]; then
        echo -e "${RED}Installation cancelled.${NC}"
        exit 0
    fi
    echo ""
}

print_banner
setup_config

# ─── Installation ──────────────────────────────────────────────────────────────
TOTAL_STEPS=12

print_step 1 $TOTAL_STEPS "System Update & Dependencies"
apt-get update -y
apt-get upgrade -y
apt-get install -y software-properties-common curl wget git unzip zip nginx sqlite3 certbot python3-certbot-nginx
print_ok "System packages installed"

print_step 2 $TOTAL_STEPS "Installing PHP 8.3+ & Extensions"
add-apt-repository -y ppa:ondrej/php
apt-get update -y
apt-get install -y php8.3 php8.3-cli php8.3-common php8.3-curl php8.3-mbstring php8.3-xml php8.3-zip php8.3-bcmath php8.3-gd php8.3-sqlite3 php8.3-intl php8.3-tokenizer php8.3-dom php8.3-fileinfo php8.3-redis php8.3-fpm
print_ok "PHP 8.3 installed"

print_step 3 $TOTAL_STEPS "Installing Node.js 22.x & npm"
curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
apt-get install -y nodejs
print_ok "Node.js $(node -v) installed"

print_step 4 $TOTAL_STEPS "Installing Composer"
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
print_ok "Composer $(composer -V | head -1) installed"

print_step 5 $TOTAL_STEPS "Setting up Web Server User"
if ! id -g devliopay >/dev/null 2>&1; then
    addgroup --system devliopay
fi
if ! id -u devliopay >/dev/null 2>&1; then
    adduser --system --ingroup devliopay --home $INSTALL_DIR --shell /bin/bash devliopay
fi
print_ok "User 'devliopay' ready"

print_info "Configuring PHP-FPM to run as devliopay..."
sed -i 's/^user = .*/user = devliopay/' /etc/php/8.3/fpm/pool.d/www.conf
sed -i 's/^group = .*/group = devliopay/' /etc/php/8.3/fpm/pool.d/www.conf
sed -i 's/^;*listen\.owner = .*/listen.owner = devliopay/' /etc/php/8.3/fpm/pool.d/www.conf
sed -i 's/^;*listen\.group = .*/listen.group = devliopay/' /etc/php/8.3/fpm/pool.d/www.conf
sed -i 's/^listen = .*/listen = \/run\/php\/php8.3-fpm.sock/' /etc/php/8.3/fpm/pool.d/www.conf
systemctl restart php8.3-fpm
# Ensure nginx can access the socket
chmod 666 /var/run/php/php8.3-fpm.sock 2>/dev/null || true

# Fix socket permissions on every FPM restart
mkdir -p /etc/systemd/system/php8.3-fpm.service.d/
cat > /etc/systemd/system/php8.3-fpm.service.d/override.conf <<OVERRIDE
[Service]
ExecStartPost=/bin/chmod 666 /run/php/php8.3-fpm.sock
OVERRIDE
systemctl daemon-reload
print_ok "PHP-FPM configured for devliopay user"

print_step 6 $TOTAL_STEPS "Cloning Repository"
if [ -d "$INSTALL_DIR" ]; then
    cd /
    rm -rf "$INSTALL_DIR"
fi
git clone https://github.com/Bebonaiem/devliopay.git "$INSTALL_DIR"
chown -R devliopay:devliopay "$INSTALL_DIR"
cd "$INSTALL_DIR"
sudo -u devliopay git config --global --add safe.directory "$INSTALL_DIR"
print_ok "Repository cloned to ${INSTALL_DIR}"

print_step 7 $TOTAL_STEPS "Installing PHP Dependencies"
sudo -u devliopay composer install --no-dev --optimize-autoloader --no-interaction
print_ok "Composer packages installed"

print_step 8 $TOTAL_STEPS "Installing Node Dependencies & Building Assets"
sudo -u devliopay npm install
sudo -u devliopay npm run build
print_ok "Frontend assets built"

print_step 9 $TOTAL_STEPS "Environment Configuration"
sudo -u devliopay cp .env.example .env
touch database/database.sqlite
chown devliopay:devliopay database/database.sqlite
chmod 664 database/database.sqlite
sudo -u devliopay php artisan key:generate --force --no-interaction

if [ "$IS_IP" = true ]; then
    APP_URL="http://${DOMAIN}"
else
    APP_URL="http://${DOMAIN}"
fi

sed -i "s|APP_URL=http://localhost|APP_URL=${APP_URL}|g" .env
sed -i "s|APP_ENV=local|APP_ENV=production|g" .env
sed -i "s|APP_DEBUG=true|APP_DEBUG=false|g" .env
print_ok "Environment configured"

print_step 10 $TOTAL_STEPS "Database Migration & Seeding"
sudo -u devliopay php artisan migrate --force --no-interaction
sudo -u devliopay php artisan db:seed --force --no-interaction
print_ok "Database migrated and seeded"

print_step 11 $TOTAL_STEPS "Creating Admin User"
sudo -u devliopay php artisan tinker --execute="
\$user = App\Models\User::create([
    'name' => '${ADMIN_NAME}',
    'email' => '${ADMIN_EMAIL}',
    'password' => bcrypt('${ADMIN_PASSWORD}'),
    'email_verified_at' => now(),
    'is_admin' => true,
]);
echo 'Admin user created: ' . \$user->email;
" 2>&1
print_ok "Admin user created: ${ADMIN_EMAIL}"

print_step 12 $TOTAL_STEPS "Permissions, Cache & Nginx"
chown -R devliopay:devliopay "$INSTALL_DIR"
chmod -R 755 "$INSTALL_DIR"
chmod -R 775 "$INSTALL_DIR/storage" 2>/dev/null || true
chmod -R 775 "$INSTALL_DIR/bootstrap/cache" 2>/dev/null || true

# Create required directories
sudo -u devliopay mkdir -p storage/logs storage/framework/views storage/framework/sessions storage/framework/cache

# Clear and build cache AS devliopay user
sudo -u devliopay php artisan storage:link --force --no-interaction
sudo -u devliopay php artisan config:cache --no-interaction
sudo -u devliopay php artisan route:cache --no-interaction
sudo -u devliopay php artisan view:cache --no-interaction
sudo -u devliopay php artisan icon:cache --no-interaction 2>/dev/null || true

# Final ownership pass after cache files are created
chown -R devliopay:devliopay "$INSTALL_DIR"
print_ok "Permissions set and cache built"

# ─── Nginx Configuration ──────────────────────────────────────────────────────
print_info "Configuring Nginx..."

PHP_FPM_SOCK="/var/run/php/php8.3-fpm.sock"

if [ "$IS_IP" = true ]; then
    NGINX_SERVER_NAME="_"
else
    NGINX_SERVER_NAME="${DOMAIN}"
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

systemctl restart php8.3-fpm
systemctl restart nginx
print_ok "Nginx configured and running"

# ─── Queue Worker ──────────────────────────────────────────────────────────────
print_info "Setting up queue worker..."

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
print_ok "Queue worker running"

# ─── Scheduler ────────────────────────────────────────────────────────────────
print_info "Setting up scheduler..."
CRON_LINE="* * * * * cd ${INSTALL_DIR} && php artisan schedule:run >> /dev/null 2>&1"
(crontab -l 2>/dev/null | grep -v "artisan schedule:run" ; echo "$CRON_LINE") | crontab -
print_ok "Scheduler configured"

# ─── SSL ───────────────────────────────────────────────────────────────────────
if [ "$IS_IP" = false ] && [ "$DOMAIN" != "localhost" ]; then
    print_info "Setting up SSL certificate..."
    certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos --email "admin@${DOMAIN}" || true
    print_ok "SSL configured"
fi

# ─── Done ──────────────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}╔══════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║            ${BOLD}DevlioPay Installed Successfully!${NC}${GREEN}               ║${NC}"
echo -e "${GREEN}╚══════════════════════════════════════════════════════════════╝${NC}"
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
