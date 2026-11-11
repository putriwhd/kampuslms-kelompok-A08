#!/usr/bin/env bash
set -euo pipefail

# Run deployment commands from the Laravel project root.
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
