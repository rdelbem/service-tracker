#!/usr/bin/env bash
set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${YELLOW}→ Stopping Service Tracker dev environment...${NC}"

# Ask if user wants to remove volumes
read -p "Do you want to remove all data (database, wordpress)? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${YELLOW}→ Removing containers and volumes...${NC}"
    docker compose down -v
    echo -e "${GREEN}✔ All data removed${NC}"
else
    echo -e "${YELLOW}→ Stopping containers (keeping data)...${NC}"
    docker compose down
    echo -e "${GREEN}✔ Containers stopped${NC}"
fi
