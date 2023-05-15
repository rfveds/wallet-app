FROM php:8.1-fpm

RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends apt-utils vim curl debconf \
    subversion git apt-transport-https \
    build-essential locales acl mailutils wget nodejs zip unzip \
    gnupg gnupg1 gnupg2 \
    zlib1g-dev \
    sudo

# Install gd extension
RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y  libfreetype6-dev libjpeg62-turbo-dev libmcrypt-dev libpng-dev \
    zlib1g-dev libxml2-dev libzip-dev libonig-dev graphviz && \
    docker-php-source extract && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install -j$(nproc) gd && \
    docker-php-source delete

# Install MySQL extension
RUN docker-php-source extract && \
    docker-php-ext-install pdo_mysql zip && \
    docker-php-source delete

# Install Intl
RUN docker-php-source extract && \
    docker-php-ext-configure intl && \
    docker-php-ext-install intl && \
    docker-php-source delete

# Setup php.ini
COPY php.ini /usr/local/etc/php/php.ini
COPY php-fpm-pool.conf 	/usr/local/etc/php/pool.d/www.conf

# Install Composer
RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls --version=2.4.1 && \
mv composer.phar /usr/local/bin/composer

# Install Symfony CLI
RUN	wget https://get.symfony.com/cli/installer -O - | bash && \
    mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# Install xdebug
RUN docker-php-source extract
RUN pecl install xdebug
RUN docker-php-ext-enable xdebug
RUN echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/error_reporting.ini
RUN echo "display_startup_errors = On" >> /usr/local/etc/php/conf.d/error_reporting.ini
RUN echo "display_errors = On" >> /usr/local/etc/php/conf.d/error_reporting.ini
RUN echo "xdebug.mode=develop,coverage,debug,trace" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.start_with_request=trigger" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.discover_client_host=1"  >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.idekey=\"PHPSTORM\"" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.var_display_max_children=128" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.var_display_max_data=512" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
RUN echo "xdebug.var_display_max_depth=3" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN docker-php-source delete

# Development tools
RUN apt-get install -y gcc g++ make

# Install Node.js
RUN curl -fsSL https://deb.nodesource.com/setup_lts.x && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y nodejs

# APT cleanup
RUN apt-get autoremove -y && \
    apt-get autoclean -y

RUN groupadd dev -g 999
RUN useradd dev -g dev -d /home/dev -m
RUN passwd -d dev

RUN rm -rf /var/lib/apt/lists/*
RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen && \
    echo "fr_FR.UTF-8 UTF-8" >> /etc/locale.gen && \
    echo "pl_PL.UTF-8 UTF-8" >> /etc/locale.gen && \
    locale-gen

RUN echo "dev ALL=(ALL) ALL" > /etc/sudoers

WORKDIR /home/wwwroot/
##</romaricp>##

EXPOSE 9000
CMD ["php-fpm"]
