FROM php:7.4.33-apache

ENV APACHE_DOCUMENT_ROOT /var/www/html/htdocs

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
RUN a2enmod rewrite headers
RUN service apache2 restart

RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN docker-php-ext-enable mysqli pdo pdo_mysql

RUN apt-get update && apt-get install -y git curl && \
    curl -fsSL https://deb.nodesource.com/setup_16.x | bash - && \
    apt-get install -y nodejs && \
    rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer self-update

COPY composer.json composer.lock ./
RUN composer install --no-interaction -vvv

COPY package.json package-lock.json ./

COPY Gruntfile.js ./
RUN npm install -g grunt-cli --verbose && \
    npm install --verbose && \
    grunt --verbose
