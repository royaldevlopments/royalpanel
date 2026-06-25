#!/bin/bash
# ────────────────────────────────────────────────────────────
# Royal Panel — Fresh Install Setup Script
# Run as root on Ubuntu 22.04
# ────────────────────────────────────────────────────────────
set -e

RED='\033[0;31m'; GREEN='\033[0;32m'; CYAN='\033[0;36m'; NC='\033[0m'
info()  { echo -e "${CYAN}[INFO]${NC} $1"; }
ok()    { echo -e "${GREEN}[OK]${NC} $1"; }
fail()  { echo -e "${RED}[FAIL]${NC} $1"; exit 1; }

# ─── Config ───────────────────────────────────────────────
DOMAIN="${DOMAIN:-panel.example.com}"
DB_NAME="${DB_NAME:-royalpanel}"
DB_USER="${DB_USER:-royalpanel}"
DB_PASS="${DB_PASS:-$(openssl rand -hex 16)}"
DB_HOST="${DB_HOST:-127.0.0.1}"
TIMEZONE="${TIMEZONE:-UTC}"
EMAIL="${EMAIL:-admin@example.com}"
USERNAME="${USERNAME:-admin}"
PASSWORD="${PASSWORD:-$(openssl rand -hex 12)}"

FOLDER="$(realpath "$(dirname "$0")")"

# ─── Root check ───────────────────────────────────────────
[[ $EUID -eq 0 ]] || fail "Run as root"

info "Starting Royal Panel install for $DOMAIN"

# ─── 1. System deps ──────────────────────────────────────
info "Installing system dependencies..."
apt update -y && apt upgrade -y
apt install -y software-properties-common curl wget git unzip \
  nginx certbot python3-certbot-nginx \
  mariadb-server mariadb-client \
  redis-server \
  composer

# PHP 8.3
add-apt-repository ppa:ondrej/php -y
apt update -y
apt install -y php8.3 php8.3-{cli,common,gd,mysql,mbstring,bcmath,xml,fpm,curl,zip,intl,redis,readline} php8.3-{bcmath,soap,sockets}
ok "System dependencies installed"

# ─── 2. Node.js 22 ───────────────────────────────────────
info "Installing Node.js 22..."
curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
apt install -y nodejs
npm install -g npx
ok "Node.js $(node -v) installed"

# ─── 3. Database ──────────────────────────────────────────
info "Setting up MariaDB..."
mysql -u root <<SQL
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'$DB_HOST' IDENTIFIED BY '$DB_PASS';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_USER'@'$DB_HOST';
FLUSH PRIVILEGES;
SQL
ok "Database $DB_NAME created"

# ─── 4. Panel files ──────────────────────────────────────
info "Setting up panel..."
cd "$FOLDER"

if [[ ! -f .env ]]; then
  cp .env.example .env
  sed -i "s|APP_URL=.*|APP_URL=https://$DOMAIN|" .env
  sed -i "s|DB_HOST=.*|DB_HOST=$DB_HOST|" .env
  sed -i "s|DB_DATABASE=.*|DB_DATABASE=$DB_NAME|" .env
  sed -i "s|DB_USERNAME=.*|DB_USERNAME=$DB_USER|" .env
  sed -i "s|DB_PASSWORD=.*|DB_PASSWORD=$DB_PASS|" .env
  sed -i "s|TIMEZONE=.*|TIMEZONE=$TIMEZONE|" .env
  php artisan key:generate --force
  ok ".env configured"
fi

# ─── 5. Composer ─────────────────────────────────────────
info "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction
ok "Composer deps installed"

# ─── 6. Migrate ──────────────────────────────────────────
info "Running migrations..."
php artisan migrate --seed --force
ok "Database migrated"

# ─── 7. Create admin user ────────────────────────────────
info "Creating admin user..."
php artisan p:user:make --email="$EMAIL" --username="$USERNAME" --password="$PASSWORD" --admin=1 2>/dev/null || \
  echo -e "${YELLOW}Admin user may already exist${NC}"
ok "Admin: $EMAIL / $PASSWORD"

# ─── 8. Set permissions ──────────────────────────────────
info "Setting permissions..."
chown -R www-data:www-data "$FOLDER"
chmod -R 755 "$FOLDER/storage" "$FOLDER/bootstrap/cache"
ok "Permissions set"

# ─── 9. Build frontend ───────────────────────────────────
info "Building frontend assets..."
npm install --production --no-interaction 2>/dev/null || true
export NODE_OPTIONS="--max-old-space-size=2048"
npx webpack --mode production 2>&1 | tail -1
ok "Frontend built"

# ─── 10. Generate bot token ──────────────────────────────
BOT_TOKEN=$(openssl rand -hex 32)
php artisan tinker --execute="\Illuminate\Support\Facades\DB::table('settings')->updateOrInsert(
  ['key' => 'settings::royal:botToken'],
  ['key' => 'settings::royal:botToken', 'value' => '$BOT_TOKEN']
);" 2>/dev/null
ok "Bot API token: $BOT_TOKEN"

# ─── 11. Crontab ─────────────────────────────────────────
info "Setting up cron..."
crontab -l 2>/dev/null | { cat; echo "* * * * * php $FOLDER/artisan schedule:run >> /dev/null 2>&1"; } | crontab -
ok "Cron installed"

# ─── 12. Queue worker systemd ────────────────────────────
info "Setting up queue worker..."
cat > /etc/systemd/system/royalpanel-queue.service <<UNIT
[Unit]
Description=Royal Panel Queue Worker
After=network.target mysql.service redis.service

[Service]
User=www-data
Group=www-data
WorkingDirectory=$FOLDER
ExecStart=/usr/bin/php $FOLDER/artisan queue:work --sleep=3 --tries=3
Restart=always

[Install]
WantedBy=multi-user.target
UNIT
systemctl daemon-reload
systemctl enable --now royalpanel-queue
ok "Queue worker running"

# ─── 13. Nginx config ────────────────────────────────────
info "Creating nginx config..."
cat > /etc/nginx/sites-available/royalpanel <<NGINX
server {
    listen 80;
    server_name $DOMAIN;

    root $FOLDER/public;
    index index.php;

    client_max_body_size 0;
    proxy_request_buffering off;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)\$;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_param PHP_VALUE "upload_max_filesize = 20480M\npost_max_size = 20480M";
    }

    location ~ /\.ht { deny all; }

    # Wings reverse proxy
    location ~ ^/(api/system|api/servers|api/transfers|api/update|api/deauthorize-user|downloads|sftp) {
        proxy_pass http://127.0.0.1:8080;
        proxy_set_header Host \$host;
        proxy_set_header X-Real-IP \$remote_addr;
        proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto \$scheme;
        proxy_buffering off;
    }
}
NGINX

ln -sf /etc/nginx/sites-available/royalpanel /etc/nginx/sites-enabled/royalpanel
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx
ok "Nginx configured"

# ─── 14. SSL via Certbot ─────────────────────────────────
info "Obtaining SSL certificate..."
certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos -m "$EMAIL" --redirect 2>/dev/null || \
  echo -e "${YELLOW}SSL skipped — run later: certbot --nginx -d $DOMAIN${NC}"
ok "SSL configured"

# ─── Summary ─────────────────────────────────────────────
echo ""
echo -e "${GREEN}════════════════════════════════════════════${NC}"
echo -e "${GREEN}  Royal Panel Installed Successfully!${NC}"
echo -e "${GREEN}════════════════════════════════════════════${NC}"
echo ""
echo -e "  Panel:  ${CYAN}https://$DOMAIN${NC}"
echo -e "  Email:  $EMAIL"
echo -e "  Pass:   $PASSWORD"
echo -e "  DB:     $DB_NAME / $DB_USER / $DB_PASS"
echo -e "  Bot:    $BOT_TOKEN"
echo ""
echo -e "  ${YELLOW}Next steps:${NC}"
echo -e "  1. Go to Admin → Royal → Advanced"
echo -e "  2. Set Discord Bot Token, Guild ID, Admin Role ID"
echo -e "  3. Install Wings: https://pterodactyl.io/wings/1.0/installing.html"
echo -e "  4. Start bot: BOT_TOKEN=$BOT_TOKEN node /root/discord-bot/index.js"
echo ""
