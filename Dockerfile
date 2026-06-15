FROM php:8.2-apache

# 1. تثبيت الإضافات والمكتبات اللازمة لـ Laravel والـ Composer
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# 2. تثبيت الـ Composer جوه السيرفر
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. نسخ ملفات المشروع بالكامل داخل السيرفر
COPY . /var/www/html

# 4. تشغيل الـ Composer لتثبيت مكاتب الـ vendor أونلاين
RUN composer install --no-dev --optimize-autoloader --no-scripts --allow-plugins

# 5. تعديل مسار Apache ليوجه لفولدر public
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/apache2.conf

# 6. إعطاء الصلاحيات اللازمة لفولدرات التخزين والكاش
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80