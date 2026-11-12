#!/usr/bin/env bash
set -euo pipefail

# Run after the new application release is deployed and production environment
# variables and database connectivity are configured. The script stops on error.
# It can be invoked from any directory; commands run from the Laravel project root.
cd "$(dirname "${BASH_SOURCE[0]}")"

# Apply database changes before rebuilding Laravel's cached configuration.
php artisan migrate --force

# Cache production configuration, routes, and compiled Blade views.
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ask any running queue workers to restart after their current job.
php artisan queue:restart

echo "Deployment tasks completed successfully."
