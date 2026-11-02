FROM php:8.4-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
      nodejs npm git curl unzip libzip-dev \
    && docker-php-ext-install mysqli pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

# Serve from /var/www/nodeman and block .env (replaces the manual 000-default.conf edits)
ENV APACHE_DOCUMENT_ROOT=/var/www/nodeman
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
      /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf \
 && printf '<FilesMatch "^\\.env">\n    Require all denied\n</FilesMatch>\n' \
      > /etc/apache2/conf-enabled/deny-env.conf

COPY . /var/www/nodeman

# Replaces the whole "permissions for www-data" section
RUN mkdir -p /var/log/nodeman /var/nodeman/data /var/www/.npm /home/nodeman/projects \
 && chown -R www-data:www-data /var/log/nodeman /var/nodeman /var/www/.npm /home/nodeman

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]
