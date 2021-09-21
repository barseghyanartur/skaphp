FROM php:7.3-fpm
WORKDIR /app

# Update the application repos
RUN apt-get update

# Install apt-utils
RUN apt-get install apt-utils -y

# Install git
RUN apt-get install git -y

# Install unzip
RUN apt-get install unzip -y

# Install 7z
RUN apt-get install p7zip-full -y

# Install xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
