.PHONY: up down wp npm install clean setup

# Start all services
up:
	docker compose up -d

# Stop all services
down:
	docker compose down

# Run WP-CLI commands. Usage: make wp cmd="plugin list"
wp:
	docker compose run --rm wpcli $(cmd)

# Install PHP dependencies
install:
	composer install

# Download WordPress core locally
setup:
	bash docker/setup-wordpress.sh

# Clean volumes (resets everything)
clean:
	docker compose down -v
	rm -rf wordpress/
