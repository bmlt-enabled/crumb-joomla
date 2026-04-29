# Joomla 5 (LTS) by default; override with `docker compose build --build-arg JOOMLA_TAG=6-php8.3-apache`
# to test against Joomla 6. The plugin and module are version-agnostic and work on 4, 5, and 6.
ARG JOOMLA_TAG=5-php8.3-apache
FROM joomla:${JOOMLA_TAG}

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
      ssl-cert mariadb-client unzip git zip && \
    a2enmod rewrite expires && \
    rm -rf /var/lib/apt/lists/*

ENV PHP_INI_PATH="/usr/local/etc/php/php.ini"

RUN pecl install xdebug && docker-php-ext-enable xdebug \
    && cp /usr/local/etc/php/php.ini-development "$PHP_INI_PATH" \
    && echo "xdebug.mode=debug" >> ${PHP_INI_PATH} \
    && echo "xdebug.client_port=9003" >> ${PHP_INI_PATH} \
    && echo "xdebug.client_host=host.docker.internal" >> ${PHP_INI_PATH} \
    && echo "xdebug.start_with_request=trigger" >> ${PHP_INI_PATH} \
    && echo "xdebug.log=/tmp/xdebug.log" >> ${PHP_INI_PATH}

COPY docker/entrypoint.sh /usr/local/bin/crumb-entrypoint.sh
RUN chmod +x /usr/local/bin/crumb-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/crumb-entrypoint.sh"]
CMD ["apache2-foreground"]
