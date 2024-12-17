FROM php:8.1-fpm

# Встановлення NGINX
RUN apt-get update && apt-get install -y nginx

# Копіюємо конфіги
COPY ./default.conf /etc/nginx/conf.d/default.conf

# Копіюємо код проекту
COPY . /var/www
WORKDIR /var/www

# Відкриття порту
EXPOSE 80

# Запуск NGINX та PHP-FPM
CMD nginx -g "daemon off;" & php-fpm
