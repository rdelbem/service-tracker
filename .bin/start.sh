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

# --- Download WordPress core locally ---
if [ ! -d "wordpress/wp-includes" ]; then
    echo -e "${YELLOW}→ Downloading WordPress core...${NC}"
    bash docker/setup-wordpress.sh
else
    echo -e "${GREEN}✔ WordPress core already downloaded${NC}"
fi

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

# --- Wait for Apache to be ready ---
echo -e "${YELLOW}→ Waiting for WordPress container to be ready...${NC}"
MAX_WAIT=60
WAITED=0
until docker compose exec wordpress curl -s -o /dev/null -w "%{http_code}" http://localhost/ | grep -q "200\|302"; do
    sleep 2
    WAITED=$((WAITED + 2))
    if [ "$WAITED" -ge "$MAX_WAIT" ]; then
        echo -e "${RED}✖ WordPress container failed to start after ${MAX_WAIT}s${NC}"
        docker compose logs wordpress
        exit 1
    fi
done

# --- Fix permissions (skip the plugin folder which is a separate bind mount) ---
mkdir -p wordpress/wp-content/uploads

# --- Generate wp-config.php if missing ---
if [ ! -f "wordpress/wp-config.php" ]; then
    echo -e "${YELLOW}→ Generating wp-config.php...${NC}"
    cp wordpress/wp-config-sample.php wordpress/wp-config.php
    sed -i 's/database_name_here/wordpress/' wordpress/wp-config.php
    sed -i 's/username_here/wp_user/' wordpress/wp-config.php
    sed -i 's/password_here/wp_password/' wordpress/wp-config.php
    sed -i 's/localhost/db/' wordpress/wp-config.php
    # Add custom port if needed
    sed -i "s/'DB_HOST', 'db'/'DB_HOST', 'db:3306'/" wordpress/wp-config.php
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
