#!/bin/bash
# Jalankan versi PRODUCTION (database kosong, port 8001)
echo "🟢 Menjalankan SI-MANTAP versi PRODUCTION..."
php artisan config:clear -q
php artisan serve --env=production --port=8001
