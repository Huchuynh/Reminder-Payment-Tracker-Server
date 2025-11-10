set -e

service cron start

# Chạy migrates
php artisan migrate --force
php artisan db:seed --class=DatabaseSeeder --force

# Start Laravel server
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
