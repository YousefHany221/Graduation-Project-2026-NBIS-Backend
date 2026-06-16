#!/bin/bash

# انتظر للتأكد من استقرار السيرفر
sleep 3

echo "🔄 Running database migrations..."
# تشغيل المايجريشن والـ output هينطبع في الـ Logs
php artisan migrate --force || echo "⚠️ Migration skipped or failed."

echo "🚀 Starting Apache Server..."
# تشغيل أباتشي بشكل صريح ومباشر
apache2-foreground