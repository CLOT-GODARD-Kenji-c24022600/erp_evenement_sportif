FROM php:8.4-fpm

# Installation des extensions nécessaires pour SQL (Alwaysdata)
RUN docker-php-ext-install pdo pdo_mysql mysqli

# On définit le répertoire de travail dans le conteneur
WORKDIR /var/www/html