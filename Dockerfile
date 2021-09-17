FROM php:7.4-fpm
WORKDIR /app

# Update the application repos
RUN apt-get update

# Install git
RUN apt-get install git -y

# Install xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
