FROM php:8.2-cli

ENV XDEBUG_MODE=off
# php-cs-fixer is not available for php 8.2 yet https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/issues/6704
ENV PHP_CS_FIXER_IGNORE_ENV=1

RUN apt update -y \
	&& apt-get install -y git unzip zip \
	&& pecl channel-update pecl.php.net \
    \
	# Opcache
	&& docker-php-ext-configure opcache --enable-opcache \
	&& docker-php-ext-enable opcache \
	\
	# XDebug
	&& pecl install xdebug \
	&& docker-php-ext-enable xdebug \
	\
	# bcmath
	&& docker-php-ext-configure bcmath --enable-bcmath \
	&& docker-php-ext-install bcmath \
    \
	# gmp
	&& apt install -y libgmp-dev \
	&& docker-php-ext-install gmp \
    \
    # cleanup
	&& rm -rf /var/cache/apk/* \
	&& docker-php-source delete

# copy our xdebug to the container
USER root
COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# set php memory limit to 1G for use when running php via the shell as makefile does this automatically
RUN echo "memory_limit = 1G" > /usr/local/etc/php/conf.d/memory-limit.ini


# composer
# disabled to keep sizes down, we'll need git and zip added to the image in order to use composer
COPY --from=composer:2.2.6 /usr/bin/composer /usr/local/bin/composer

WORKDIR /app

# uncomment this if you want to include the code in the image
# as we are always volume mounting this isn't needed and will just increase the size massively
# COPY . .

CMD ["php", "run.php"]