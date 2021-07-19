FROM php:8.0-apache

EXPOSE 80

# Install developer dependencies
#ARG DEBIAN_FRONTEND=noninteractive
RUN apt-get update -yqq && \
#    apt-get install -yq apt-utils && \
    apt-get install -y nano && \
    apt-get install -y git \
    bison libsqlite3-dev libxml2-dev libicu-dev libfreetype6-dev \
    libmcrypt-dev libjpeg62-turbo-dev libpng-dev libcurl4-gnutls-dev libbz2-dev \
    libonig-dev libssl-dev libzip-dev -yqq 
    
# Install php extensions
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install pdo_sqlite
RUN docker-php-ext-install opcache
RUN docker-php-ext-install calendar
RUN docker-php-ext-install bcmath
RUN docker-php-ext-install zip
RUN docker-php-ext-install bz2
RUN docker-php-ext-install intl
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd


# Install PECL extensions
RUN pear config-set php_ini "$PHP_INI_DIR"
RUN pecl channel-update pecl.php.net
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug

#enable remote debugging by adding to php.ini :
#for XDebug 2 - RUN echo "xdebug.remote_enable=on" >>  $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.mode=debug" >>  $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini 

#for XDebug 2 - RUN echo "xdebug.remote_autostart=on" >>  $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.start_with_request=yes" >>  $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini 

#for XDebug 2 - RUN echo "xdebug.remote_connect_back=on" >>  $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini 
RUN echo "xdebug.discover_client_host=on" >>  $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini 

RUN echo "xdebug.client_port=9003" >>  $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini  


#for XDebug 2 - RUN echo "xdebug.remote_log=\"/tmp/xdebug.log\"" >>  $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.log=\"/tmp/xdebug.log\"" >>  $PHP_INI_DIR/conf.d/docker-php-ext-xdebug.ini



# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=/usr/bin --filename=composer
RUN php -r "unlink('composer-setup.php');"

RUN a2enmod rewrite


 






