#!/bin/bash
# Jalankan versi DEMO (data seeder, port 8000)
echo "🟡 Menjalankan SI-MANTAP versi DEMO..."
cp .env.demo .env 2>/dev/null || echo "  (menggunakan .env yang ada)"
php artisan config:clear -q
php artisan serve --port=8000
