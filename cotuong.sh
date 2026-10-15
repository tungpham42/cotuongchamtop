#!/bin/bash

rm .git/index.lock
git stash
git pull origin chu-kha
php artisan migrate
php artisan optimize:clear
php artisan optimize
php artisan storage:link
