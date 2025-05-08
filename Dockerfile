# Gunakan image resmi PHP dengan Apache
FROM php:8.2-apache

# Install ekstensi PHP yang dibutuhkan Laravel
RUN apt-get update && apt-get install -y \
    zip unzip libzip-dev libpng-dev libonig-dev libxml2-dev curl git \
    && docker-php-ext-install pdo_mysql mbstring zip exif pcntl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Salin semua file Laravel ke dalam kontainer
COPY . /var/www/html

# Set working directory
WORKDIR /var/www/html

# Install dependency Laravel
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Set permission storage dan bootstrap
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Aktifkan mod_rewrite
RUN a2enmod rewrite

# Set environment production
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# Ubah konfigurasi apache agar pakai folder public Laravel
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Port default
EXPOSE 80

# Jalankan Apache saat container start
CMD ["apache2-foreground"]
