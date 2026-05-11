#!/bin/sh
set -eu

if [ "${LOCALCAPITAL_AUTO_APPLY_CONTENT:-1}" != "0" ]; then
  php /var/www/html/scripts/apply-content-updates.php
fi

exec docker-php-entrypoint "$@"
