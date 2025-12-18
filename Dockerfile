FROM php:8.5-cli

ENV XDEBUG_MODE=off
ENV PHP_CS_FIXER_IGNORE_ENV=1

# Install system dependencies
RUN apt-get update -y \
	&& apt-get install -y --no-install-recommends \
		git \
		unzip \
		zip \
		libgmp-dev \
	&& rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN pecl channel-update pecl.php.net \
	# XDebug
	&& pecl install xdebug \
	&& docker-php-ext-enable xdebug \
	# bcmath
	&& docker-php-ext-install bcmath \
	# gmp
	&& docker-php-ext-install gmp \
	# Cleanup
	&& docker-php-source delete

# Copy configuration files
COPY xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
RUN echo "memory_limit = 1G" > /usr/local/etc/php/conf.d/memory-limit.ini

# Install latest composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Fix git safe.directory for mounted volumes
RUN git config --system --add safe.directory '*'

WORKDIR /app

CMD ["php", "run.php"]