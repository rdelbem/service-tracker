#!/usr/bin/env bash
set -e

WP_DIR="./wordpress"

if [ -d "$WP_DIR/wp-includes" ]; then
    echo "WordPress core already downloaded."
    exit 0
fi

echo "Downloading WordPress core to $WP_DIR ..."

mkdir -p "$WP_DIR"

curl -sS https://wordpress.org/latest.tar.gz | tar xz --strip-components=1 -C "$WP_DIR"

# Remove the default plugin symlink placeholder if it exists
rm -rf "$WP_DIR/wp-content/plugins/service-tracker"

echo "Done! WordPress core is available at $WP_DIR/"
