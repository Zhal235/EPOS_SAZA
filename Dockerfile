FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM composer:2.6 AS deps
WORKDIR /app
COPY composer.json composer.lock ./
# Ignore platform requirements to avoid errors with missing extensions in the composer image
# The actual runtime image will have the necessary extensions
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs

FROM php:8.2-fpm-alpine

# Install dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    git \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    oniguruma-dev \
    icu-dev

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl zip \
    && apk add --no-cache $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

# ========================================
# 🚀 PHP OPTIMIZATION CONFIGURATION
# ========================================

# Create PHP config directory if not exists
RUN mkdir -p /usr/local/etc/php/conf.d

# PHP-FPM Memory and Upload Optimization
RUN echo "memory_limit = 512M" > /usr/local/etc/php/conf.d/memory.ini \
    && echo "upload_max_filesize = 20M" >> /usr/local/etc/php/conf.d/memory.ini \
    && echo "post_max_size = 20M" >> /usr/local/etc/php/conf.d/memory.ini \
    && echo "max_execution_time = 60" >> /usr/local/etc/php/conf.d/memory.ini \
    && echo "max_input_time = 60" >> /usr/local/etc/php/conf.d/memory.ini

# OPcache Configuration (CRITICAL for Laravel Performance)
RUN echo "opcache.enable=1" > /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=16" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.save_comments=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.fast_shutdown=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && docker-php-ext-enable opcache

# Realpath Cache (Important for Laravel file resolution)
RUN echo "realpath_cache_size=4096K" > /usr/local/etc/php/conf.d/realpath.ini \
    && echo "realpath_cache_ttl=600" >> /usr/local/etc/php/conf.d/realpath.ini

# ========================================

# Configure Nginx
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisor.conf /etc/supervisord.conf

# Setup working directory
WORKDIR /var/www/html

# Copy extensions and dependencies
COPY --from=deps /app/vendor /var/www/html/vendor
COPY . .
COPY --from=assets /app/public/build /var/www/html/public/build

# Install composer for the final stage
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Finish composer setup with optimization
RUN composer dump-autoload --optimize --classmap-authoritative

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache \
    && chmod +x /var/www/html/docker/entrypoint.sh

# Expose port
EXPOSE 80

# Healthcheck
HEALTHCHECK --interval=30s --timeout=3s \
  CMD curl -f http://localhost/ || exit 1

# Start supervisor
ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
