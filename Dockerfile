FROM php:7.4-fpm
WORKDIR /app

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

#RUN pecl install mbstring \
#    && docker-php-ext-enable mbstring

#RUN pecl install json \
#    && docker-php-ext-enable json

#RUN pecl install mcrypt \
#    && docker-php-ext-enable mcrypt

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
