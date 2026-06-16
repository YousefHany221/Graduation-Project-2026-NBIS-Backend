#!/bin/bash

# انتظر 3 ثواني للتأكد من استقرار السيرفر
sleep 3

# تشغيل الـ Migrations بشكل آمن
# علامة (|| true) بتضمن إن لو الأمر فشل لأي سبب، السيرفر ما يقعش ويكمل قومة
echo "🔄 Running database migrations..."
php artisan migrate --force || echo "⚠️ Migration failed, but keeping container alive..."

# تشغيل Apache في الـ Foreground كالمعتاد
echo "🚀 Starting Apache Server..."
exec apache2-foreground