#!/bin/sh
set -e

cd /app

if [ -z "${APP_KEY}" ]; then
	echo "APP_KEY is empty. Generate one with 'php artisan key:generate --show' and set it in the app environment." >&2
	exit 1
fi

# storage/ is a persistent volume: the tree may be empty on first boot.
mkdir -p \
	storage/app/public \
	storage/framework/cache/data \
	storage/framework/sessions \
	storage/framework/views \
	storage/logs
chown -R www-data:www-data storage bootstrap/cache

artisan() {
	su www-data -s /bin/sh -c "php /app/artisan $*"
}

attempt=0
until artisan "db:show --quiet" >/dev/null 2>&1; do
	attempt=$((attempt + 1))
	if [ "$attempt" -ge 30 ]; then
		echo "Database still unreachable after 30 attempts, aborting." >&2
		exit 1
	fi
	echo "Waiting for the database... ($attempt/30)"
	sleep 2
done

artisan "migrate --force --isolated"

# route:cache est ignoré : routes/web.php contient des routes closure.
artisan "config:cache"
artisan "view:cache"
artisan "event:cache"

exec /usr/bin/supervisord -c /etc/supervisor/supervisord.conf
