FROM wordpress:php8.2-apache

# Copy all themes in this repo into the container
COPY ./wp-content/themes/ /var/www/html/wp-content/themes/

# Permissions for Apache user
RUN chown -R www-data:www-data /var/www/html/wp-content/themes/
