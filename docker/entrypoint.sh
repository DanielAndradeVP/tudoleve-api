#!/usr/bin/env bash
set -euo pipefail

DB_HOST="${DB_HOST:-db}"
DB_PORT="${DB_PORT:-3306}"

echo "Waiting for MySQL at ${DB_HOST}:${DB_PORT}..."
until (echo >"/dev/tcp/${DB_HOST}/${DB_PORT}") >/dev/null 2>&1; do
  sleep 1
done
echo "MySQL is reachable."

# Only recurse when the named volume was just created (root-owned).
# On subsequent restarts, ownership is already correct — avoids expensive recursion.
if [ "$(stat -c %U storage)" != "www-data" ]; then
  chown -R www-data:www-data storage bootstrap/cache
fi
chmod -R ug+rw storage bootstrap/cache

exec "$@"
