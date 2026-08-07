# Panduan Setup SI-MANTAP untuk Developer

## Prasyarat
- PHP 8.3+
- Composer 2.x
- Node.js 20+ & NPM
- MySQL 8.0
- Git

## Langkah Setup

### 1. Clone & Install
```bash
git clone <repository-url> simantap-app
cd simantap-app

# Install dependensi PHP
composer install

# Install dependensi JavaScript
npm install
```

### 2. Konfigurasi Environment
```bash
cp .env.example .env
php artisan key:generate
```

Edit file `.env`:
```
DB_DATABASE=simantap
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. Setup Database
```bash
# Buat database MySQL terlebih dahulu
mysql -u root -p -e "CREATE DATABASE simantap CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Jalankan migrasi
php artisan migrate

# Seed data awal (WHO reference + akun demo + data dummy)
php artisan db:seed
```

### 4. Build Assets
```bash
# Development (hot reload)
npm run dev

# Production build
npm run build
```

### 5. Jalankan Server
```bash
php artisan serve
# Buka: http://localhost:8000
```

## Akun Demo

| Email | Password | Role |
|-------|----------|------|
| admin@simantap.dev | password | Admin |
| kader@simantap.dev | password | Kader Posyandu |
| petugas@simantap.dev | password | Petugas Puskesmas |
| dinas@simantap.dev | password | Dinas Kesehatan |

## Data WHO Z-Score

Seeder `WhoZscoreSeeder` hanya berisi data SAMPEL (beberapa titik usia).
Untuk data LENGKAP (0-60 bulan, ~380 baris), ikuti langkah berikut:

### Download Data WHO
1. Buka https://www.who.int/tools/child-growth-standards/standards
2. Download file berikut ke folder `database/data/`:
   - `wfa-boys-zscore-who.csv` (BB/U Laki-laki)
   - `wfa-girls-zscore-who.csv` (BB/U Perempuan)
   - `lhfa-boys-zscore-who.csv` (TB/U Laki-laki)
   - `lhfa-girls-zscore-who.csv` (TB/U Perempuan)
   - `wfl-boys-zscore-who.csv` (BB/TB Laki-laki, length-based)
   - `wfl-girls-zscore-who.csv` (BB/TB Perempuan)

3. Jalankan seeder data lengkap:
   ```bash
   php artisan db:seed --class=WhoZscoreFullSeeder
   ```

## Struktur Direktori Penting

```
app/
├── Services/
│   ├── ZScoreCalculator.php    ← Kalkulasi Z-score metode LMS
│   ├── EwsEngine.php           ← Engine deteksi weight faltering
│   └── NotificationDispatcher.php
├── Observers/
│   └── PengukuranObserver.php  ← Otomatis trigger EWS saat data baru
└── Models/
    └── WhoZscoreReference.php  ← Model untuk tabel referensi WHO
```

## Testing

### Validasi Z-Score
Bandingkan output `ZScoreCalculator` dengan WHO Anthro Software:
```bash
# Contoh via Tinker
php artisan tinker
>>> app(App\Services\ZScoreCalculator::class)->calcZ(10.2, 12, 'L', 'BB_U')
# Harus menghasilkan nilai mendekati 0 (median usia 12 bulan laki-laki)
```

### Run Alpha Testing Dataset
```bash
php artisan test --filter=ZScoreTest
```

## Deployment ke Railway (Free Tier)

1. Push ke GitHub
2. Buat project baru di railway.app
3. Tambah service MySQL dari Railway
4. Set environment variables di Railway dashboard
5. Deploy otomatis via GitHub

## Troubleshooting

**Error: Class 'App\Models\Wilayah' not found**
```bash
composer dump-autoload
```

**Error: SQLSTATE Connection refused**
Pastikan MySQL berjalan: `sudo service mysql start`

**Z-score mengembalikan null**
Data WHO di tabel `who_zscore_reference` belum lengkap.
Jalankan ulang: `php artisan db:seed --class=WhoZscoreSeeder`
