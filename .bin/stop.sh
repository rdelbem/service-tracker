#!/usr/bin/env bash
set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${YELLOW}→ Stopping Service Tracker dev environment...${NC}"
docker compose down
echo -e "${GREEN}✔ Done${NC}"
