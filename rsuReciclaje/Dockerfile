# Dockerfile para aplicación Laravel con PHP 8.2-FPM
FROM php:8.2-fpm

# Información del mantenedor
LABEL maintainer="rsu-reciclaje"
LABEL description="Aplicación Laravel RSU Reciclaje"

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    netcat-openbsd \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos de la aplicación
COPY . /var/www/html

# Instalar dependencias de PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copiar script de inicialización
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Configurar permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Exponer puerto 9000 para PHP-FPM
EXPOSE 9000

# Usar script de inicialización
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php-fpm"]








