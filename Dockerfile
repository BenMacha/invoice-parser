FROM composer:2 as composer

FROM php:8.2-fpm

# Installer les dépendances nécessaires pour PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    curl \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql zip

# Installer Composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Nettoyer le cache apt pour alléger l'image
RUN apt-get clean && rm -rf /var/lib/apt/lists/*
