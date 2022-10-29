#!/bin/sh
set -e

if [ $APP_ENV == 'local' ]; then
    if [ ! -d "/app/node_modules" ]; then
        echo "No node_modules directory found, installing dependencies"
        npm install
    fi
    if [ ! -d "/app/vendor" ]; then
        echo "No vendor directory found, installing dependencies"
        XDEBUG_MODE=off composer install --ignore-platform-reqs
    fi
    XDEBUG_MODE=off php artisan octane:install
    XDEBUG_MODE=off php artisan migrate --force
    XDEBUG_MODE=off php artisan db:seed --force
fi
