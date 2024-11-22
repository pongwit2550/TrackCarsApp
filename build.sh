#!/bin/bash

# ติดตั้ง Composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# ติดตั้ง PHP dependencies
composer install
