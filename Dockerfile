FROM php:8.1-cli

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer
RUN apt-get update && apt-get install -y libmagickwand-dev libzip-dev zip --no-install-recommends && rm -rf /var/lib/apt/lists/*
RUN pecl install imagick && docker-php-ext-enable imagick && docker-php-ext-install zip
RUN alias composer='php /usr/bin/composer'

WORKDIR /app
