FROM php:8.2-apache

# 1. أداة تثبيت إضافات PHP الجاهزة
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    apt-get update && apt-get install -y git unzip && \
    install-php-extensions pdo_mysql zip

# 2. تفعيل الـ Apache Rewrite Module
RUN a2enmod rewrite

# 3. تثبيت الـ Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. نسخ ملفات المشروع
COPY . /var/www/html

# 5. تشغيل الـ Composer
RUN composer install --no-dev --optimize-autoloader --no-scripts --ignore-platform-reqs

# 6. مسار Apache لـ public
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/apache2.conf

# 7. الصلاحيات
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# -------------------------------------------------------------
# 8. إعداد سكريبت الـ Entrypoint الجديد
# -------------------------------------------------------------
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

# تشغيل السكريبت السحري عند بدء الحاوية
ENTRYPOINT ["entrypoint.sh"]