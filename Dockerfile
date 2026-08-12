# Development image of the package.
#
# It only contains PHP, Composer and the tools needed to run the test suite and
# the quality checks. The package itself is mounted into /app at runtime, so the
# image does not have to be rebuilt while working on the code.
#
#   docker build --build-arg PHP_VERSION=8.4 --tag payment-dev .
#   docker run --rm --volume "$PWD":/app payment-dev composer install
#   docker run --rm --volume "$PWD":/app payment-dev phpunit
#
# The Makefile that ships with the package wraps those commands.

ARG PHP_VERSION=8.4

FROM php:${PHP_VERSION}-cli

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# pdo_mysql is not used by the package, but Laravel's database configuration
# reads constants off it when it is there, so the CI runners have it and the
# test suite has to see the same deprecations they do.
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libicu-dev libxml2-dev \
    && docker-php-ext-install -j"$(nproc)" intl soap pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# pcov is the code coverage driver of the test suite. It is built from source
# because not every network can reach pecl.
RUN apt-get update \
    && apt-get install -y --no-install-recommends $PHPIZE_DEPS \
    && git clone --depth 1 --branch v1.0.12 https://github.com/krakjoe/pcov.git /tmp/pcov \
    && cd /tmp/pcov \
    && phpize \
    && ./configure --enable-pcov \
    && make -j"$(nproc)" \
    && make install \
    && docker-php-ext-enable pcov \
    && cd / \
    && rm -rf /tmp/pcov \
    && apt-get purge -y --auto-remove $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/*

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer \
    COMPOSER_NO_INTERACTION=1 \
    PATH=/app/vendor/bin:$PATH

WORKDIR /app

CMD ["php", "-v"]
