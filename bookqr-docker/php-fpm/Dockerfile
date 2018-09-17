FROM php:7.2.2-fpm

RUN apt-get update && apt-get install -y \
    zip unzip zlib1g-dev \
    mysql-client \
    --no-install-recommends \
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install zip

#installing git
RUN apt-get install git -y

# installing composer
RUN curl -sS https://getcomposer.org/installer | php \
        && mv composer.phar /usr/local/bin/ \
        && ln -s /usr/local/bin/composer.phar /usr/local/bin/composer
