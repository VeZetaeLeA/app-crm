FROM php:8.2-apache

# Habilitar mod_rewrite para el router MVC (App.php)
RUN a2enmod rewrite

# Actualizar el document root al directorio public/
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Instalar dependencias del sistema y extensiones de PHP necesarias
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_mysql zip

# Instalar extensión de Redis
RUN pecl install redis && docker-php-ext-enable redis

# Limpieza de cache de APT para reducir tamaño de la imagen
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
