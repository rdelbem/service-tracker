#!/usr/bin/env bash
set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}=== Service Tracker Dev Environment ===${NC}"

# --- Check dependencies ---
if ! docker compose version &> /dev/null; then
    echo -e "${RED}✖ Docker Compose is not installed.${NC}"
    exit 1
fi

if ! command -v composer &> /dev/null; then
    echo -e "${RED}✖ Composer is not installed.${NC}"
    exit 1
fi

echo -e "${GREEN}✔ Dependencies check passed${NC}"

# --- WordPress core will be auto-downloaded by the official WordPress image ---
# The wordpress_data volume will persist the core files after first run
echo -e "${GREEN}✔ WordPress core check skipped (auto-managed by container)${NC}"

# --- Install PHP dependencies ---
if [ ! -d "vendor" ]; then
    echo -e "${YELLOW}→ Installing PHP dependencies...${NC}"
    composer install
else
    echo -e "${GREEN}✔ PHP dependencies already installed${NC}"
fi

# --- Start Docker services ---
if docker compose ps --services 2>/dev/null | grep -q "wordpress"; then
    echo -e "${YELLOW}→ Containers already running. Restarting...${NC}"
    docker compose down
fi

echo -e "${YELLOW}→ Starting Docker services...${NC}"
docker compose up -d db wordpress phpmyadmin

# --- Wait for database to be ready ---
echo -e "${YELLOW}→ Waiting for database to be ready...${NC}"
MAX_WAIT=60
WAITED=0
until docker compose exec db mysqladmin ping -h localhost --silent 2>/dev/null; do
    sleep 2
    WAITED=$((WAITED + 2))
    if [ "$WAITED" -ge "$MAX_WAIT" ]; then
        echo -e "${RED}✖ Database failed to start after ${MAX_WAIT}s${NC}"
        docker compose logs db
        exit 1
    fi
done
echo -e "${GREEN}✔ Database is ready${NC}"

# --- Wait for WordPress container to be ready ---
echo -e "${YELLOW}→ Waiting for WordPress container to initialize...${NC}"
MAX_WAIT=60
WAITED=0
until docker compose exec wordpress curl -s -o /dev/null -w "%{http_code}" http://localhost/ 2>/dev/null | grep -q "200\|301\|302"; do
    sleep 2
    WAITED=$((WAITED + 2))
    if [ "$WAITED" -ge "$MAX_WAIT" ]; then
        echo -e "${RED}✖ WordPress container failed to start after ${MAX_WAIT}s${NC}"
        docker compose logs wordpress
        exit 1
    fi
done
echo -e "${GREEN}✔ WordPress container is ready${NC}"

# --- Fix permissions ---
docker compose exec wordpress mkdir -p /var/www/html/wp-content/uploads

# --- Generate wp-config.php if missing ---
WP_CONFIG_EXISTS=$(docker compose exec wordpress test -f /var/www/html/wp-config.php 2>/dev/null && echo "yes" || echo "no")
if [ "$WP_CONFIG_EXISTS" = "no" ]; then
    echo -e "${YELLOW}→ Creating wp-config.php...${NC}"
    docker compose exec wordpress bash -c "cat > /var/www/html/wp-config.php <<'EOF'
<?php
define( 'DB_NAME', 'wordpress' );
define( 'DB_USER', 'wp_user' );
define( 'DB_PASSWORD', 'wp_password' );
define( 'DB_HOST', 'db:3306' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );
define( 'WP_HOME', 'http://localhost:8080' );
define( 'WP_SITEURL', 'http://localhost:8080' );

\$table_prefix = 'wp_';

define( 'WP_DEBUG', true );

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
EOF"
    echo -e "${GREEN}✔ wp-config.php created${NC}"
fi

echo -e "${GREEN}✔ WordPress container is ready${NC}"

# --- Check if WordPress is already installed ---
IS_INSTALLED=$(docker compose run --rm wpcli core is-installed 2>/dev/null && echo "yes" || echo "no")

if [ "$IS_INSTALLED" = "no" ]; then
    echo -e "${YELLOW}→ Installing WordPress...${NC}"
    docker compose run --rm wpcli core install \
        --url=http://localhost:8080 \
        --title="Service Tracker Dev" \
        --admin_user=admin \
        --admin_password=admin \
        --admin_email=admin@example.com \
        --skip-email

    echo -e "${GREEN}✔ WordPress installed!${NC}"
else
    echo -e "${GREEN}✔ WordPress already installed${NC}"
fi

# --- Activate the plugin ---
echo -e "${YELLOW}→ Activating Service Tracker plugin...${NC}"
docker compose run --rm wpcli plugin activate service-tracker 2>/dev/null || true

echo ""
echo -e "${GREEN}===========================================${NC}"
echo -e "${GREEN}  Dev environment is ready! 🚀${NC}"
echo -e "${GREEN}===========================================${NC}"
echo -e ""
echo -e "  WordPress:  http://localhost:8080"
echo -e "  Admin:      http://localhost:8080/wp-admin"
echo -e "  phpMyAdmin: http://localhost:8081"
echo -e ""
echo -e "  User: ${YELLOW}admin${NC}"
echo -e "  Pass: ${YELLOW}admin${NC}"
echo -e ""
echo -e "  WP-CLI:   ${YELLOW}.bin/wp <command>${NC}"
echo -e "  Stop:     ${YELLOW}.bin/stop${NC}"
echo -e ""
