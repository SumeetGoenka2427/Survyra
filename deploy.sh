#!/bin/bash
# Runs on the Hostinger server, in the app directory, after `git pull`.
# Safe to re-run — every step here is idempotent.
set -e

composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
