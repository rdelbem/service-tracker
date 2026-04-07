.PHONY: up down wp npm install clean setup phpcs phpcbf phpstan phpstan:baseline test fix

# Start all services
up:
	docker compose up -d

# Stop all services
down:
	docker compose down -v

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

# ============================================================
# PHP Code Quality Tools
# ============================================================

# Run PHP CodeSniffer to check for coding standard violations
phpcs:
	vendor/bin/phpcs --standard=phpcs.xml.dist

# Auto-fix coding standard violations
phpcbf:
	vendor/bin/phpcbf --standard=phpcs.xml.dist

# Run PHPStan static analysis
phpstan:
	vendor/bin/phpstan analyse --memory-limit=2G

# Generate PHPStan baseline (use when introducing PHPStan to existing code)
phpstan:baseline:
	vendor/bin/phpstan analyse --memory-limit=2G --generate-baseline=phpstan-baseline.neon

# Run all tests (PHPCS + PHPStan)
test: phpcs phpstan

# Auto-fix what can be auto-fixed
fix: phpcbf
