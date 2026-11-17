FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends poppler-utils \
    && docker-php-ext-install pdo_mysql \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN { \
      echo "display_errors=Off"; \
      echo "log_errors=On"; \
      echo "expose_php=Off"; \
      echo "upload_max_filesize=8M"; \
      echo "post_max_size=10M"; \
    } > /usr/local/etc/php/conf.d/smbd-prod.ini \
    && { \
      echo "ServerTokens Prod"; \
      echo "ServerSignature Off"; \
    } > /etc/apache2/conf-available/smbd-security.conf \
    && a2enconf smbd-security

WORKDIR /var/www/html
COPY . /var/www/html

RUN mkdir -p /var/www/html/storage/logs /var/www/html/storage/uploads \
    && chown -R www-data:www-data /var/www/html/storage \
    && chmod -R 775 /var/www/html/storage

EXPOSE 80

CMD ["sh", "-c", "PORT=${PORT:-80}; sed -ri \"s/Listen 80/Listen ${PORT}/g\" /etc/apache2/ports.conf && sed -ri \"s/<VirtualHost \\*:80>/<VirtualHost *:${PORT}>/g\" /etc/apache2/sites-available/000-default.conf && exec apache2-foreground"]
