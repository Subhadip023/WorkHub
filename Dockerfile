# =========================
# 1️⃣ Frontend build stage
# =========================
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package*.json ./
RUN npm install

COPY resources resources
COPY vite.config.js tailwind.config.js postcss.config.js ./

RUN npm run build


# =========================
# 2️⃣ Backend (PHP + Nginx)
# =========================
FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl unzip nginx \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev libzip-dev libicu-dev \
    libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_pgsql pgsql mbstring bcmath gd zip intl exif \
    && rm /etc/nginx/sites-enabled/default


# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy backend files
COPY . .

# ✅ Copy built frontend assets
COPY --from=frontend /app/public/build public/build

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Laravel permissions
RUN chown -R www-data:www-data storage bootstrap/cache

# Copy Nginx config
COPY nginx/default.conf /etc/nginx/conf.d/default.conf

EXPOSE 80
CMD php-fpm -D && nginx -g 'daemon off;'
