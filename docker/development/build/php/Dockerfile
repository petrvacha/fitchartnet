FROM php:7.4.33-apache

ENV APACHE_DOCUMENT_ROOT /var/www/html/htdocs

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite headers
RUN service apache2 restart

# Instalace ZIP extension a dalších PHP extensions + Node.js
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-install zip mysqli pdo pdo_mysql \
    && docker-php-ext-enable zip mysqli pdo pdo_mysql \
    && curl -fsSL https://deb.nodesource.com/setup_16.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Instalace Composer (bez self-update, protože composer:latest je už aktuální)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Kopírování entrypoint scriptu
COPY docker/development/build/php/docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
