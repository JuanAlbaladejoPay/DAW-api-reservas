FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libonig-dev \
    libzip-dev \
    zip \
    unzip \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo mbstring exif pcntl bcmath gd zip

# Crear usuario no-root
RUN useradd -m symfonyuser

WORKDIR /var/www

# Cambiar el propietario del directorio de trabajo
RUN chown -R symfonyuser:symfonyuser /var/www

# Descargar e instalar Composer como root
USER root
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Cambiar al usuario no-root
USER symfonyuser

# Verificar la instalación de Composer
RUN /usr/local/bin/composer --version

# Copiar archivos de configuración de Composer
COPY --chown=symfonyuser:symfonyuser composer.json composer.lock ./

# Instalar dependencias de Symfony
RUN /usr/local/bin/composer install --no-scripts

# Copiar el resto de los archivos de la aplicación
COPY --chown=symfonyuser:symfonyuser . .

# Ejecutar los scripts de Symfony después de copiar los archivos
RUN /usr/local/bin/composer run-script post-install-cmd

EXPOSE 9000

CMD ["php-fpm"]
