#!/bin/sh
set -e
mkdir -p bootstrap/cache storage/framework/cache storage/framework/sessions storage/framework/views storage/logs public/uploads
chmod -R 775 bootstrap/cache storage public/uploads

# Ensure .env exists (e.g. from container env or .env.example)
test -f .env || cp .env.example .env

# Sync all Coolify-injected env into .env so Laravel picks them up (uses # delimiter to avoid breaking on | in values)
if [ -f .env ]; then
  _set() { _v=$(eval "echo \$$1"); [ -n "$_v" ] && sed -i "s#^$1=.*#$1=$_v#" .env 2>/dev/null || true; }
  _set APP_NAME; _set APP_ENV; _set APP_KEY; _set APP_DEBUG; _set APP_URL
  _set APP_LOCALE; _set APP_FALLBACK_LOCALE; _set APP_FAKER_LOCALE; _set APP_MAINTENANCE_DRIVER
  _set BCRYPT_ROUNDS; _set LOG_CHANNEL; _set LOG_STACK; _set LOG_DEPRECATIONS_CHANNEL; _set LOG_LEVEL
  _set DB_CONNECTION; _set DB_HOST; _set DB_PORT; _set DB_DATABASE; _set DB_USERNAME; _set DB_PASSWORD
  _set SESSION_DRIVER; _set SESSION_LIFETIME; _set SESSION_ENCRYPT; _set SESSION_PATH; _set SESSION_DOMAIN
  _set BROADCAST_CONNECTION; _set FILESYSTEM_DISK; _set QUEUE_CONNECTION; _set CACHE_STORE; _set MEMCACHED_HOST
  _set REDIS_CLIENT; _set REDIS_SOCKET; _set REDIS_HOST; _set REDIS_PASSWORD; _set REDIS_PORT
  _set MAIL_MAILER; _set MAIL_SCHEME; _set MAIL_HOST; _set MAIL_PORT; _set MAIL_USERNAME; _set MAIL_PASSWORD
  _set MAIL_FROM_ADDRESS; _set MAIL_FROM_NAME
  _set AWS_ACCESS_KEY_ID; _set AWS_SECRET_ACCESS_KEY; _set AWS_DEFAULT_REGION; _set AWS_BUCKET; _set AWS_USE_PATH_STYLE_ENDPOINT
  _set VITE_APP_NAME; _set PORT
fi

php artisan optimize:clear || true
php artisan migrate --force || true
exec php artisan serve --host=0.0.0.0 --port="${PORT:-80}"
