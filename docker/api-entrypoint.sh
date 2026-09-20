#!/bin/sh
# filepath: docker/api-entrypoint.sh
# On container startup, make sure:
#   1. api_php/admin and rosefinch_data exist
#   2. Both directories are writable by the in-container PHP user and the host user
# Then exec the original command to start php-fpm.

set -e

mkdir -p /var/www/html/phpfm_api/admin
mkdir -p /var/www/html/phpfm_api/log
mkdir -p /var/www/rosefinch_data

# chmod 777 lets both the in-container PHP user and the host user
# write to these directories. This is for local development only.
chmod -R 777 /var/www/html/phpfm_api/admin
chmod -R 777 /var/www/html/phpfm_api/log
chmod -R 777 /var/www/rosefinch_data

# Hand off to the upstream docker-php-entrypoint, asking it to start php-fpm.
exec docker-php-entrypoint php-fpm
