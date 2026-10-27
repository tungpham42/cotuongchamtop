#!/bin/bash

php artisan migrate
php artisan optimize:clear
php artisan optimize
php artisan storage:link
php artisan serve
