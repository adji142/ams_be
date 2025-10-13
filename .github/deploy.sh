#!/bin/bash

# Script untuk melakukan deployment aplikasi Laravel
# Hentikan script jika terjadi error
set -e

# --- KONFIGURASI ---
# Ganti dengan URL repository Git Anda
REPO_URL="https://github.com/username/repo.git"
# Direktori tujuan di server tempat aplikasi akan di-clone
PROJECT_PATH="/var/www/asset-management"
# User dan grup web server (umumnya www-data untuk Nginx/Apache di Ubuntu/Debian)
WEB_USER="www-data"
WEB_GROUP="www-data"

# --- PROSES DEPLOYMENT ---

echo "Memulai proses deployment..."

# 1. Clone repository dari Git
# Hapus folder lama jika ada untuk memastikan clone yang bersih
if [ -d "$PROJECT_PATH" ]; then
    echo "Menghapus direktori proyek yang lama..."
    rm -rf $PROJECT_PATH
fi
echo "Melakukan clone repository dari $REPO_URL..."
git clone $REPO_URL $PROJECT_PATH

# 2. Masuk ke direktori proyek
cd $PROJECT_PATH

# 3. Install dependensi PHP dengan Composer
echo "Menginstall dependensi Composer (mode produksi)..."
composer install --no-dev --optimize-autoloader

# 4. Install dependensi Node.js dan build asset
# Periksa apakah package.json ada sebelum menjalankan npm
if [ -f "package.json" ]; then
    echo "Menginstall dependensi NPM..."
    npm install
    echo "Melakukan build asset front-end (npm run prod)..."
    npm run prod
fi

# 5. Setup file environment (.env)
echo "Membuat file .env dari .env.example..."
cp .env.example .env

# 6. Generate application key
echo "Menghasilkan APP_KEY baru..."
php artisan key:generate

# --- INTERAKSI MANUAL ---
echo "----------------------------------------------------------------"
echo "!!! TINDAKAN DIPERLUKAN !!!"
echo "Harap edit file .env di $PROJECT_PATH sekarang."
echo "Isi konfigurasi database (DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD) dan konfigurasi lainnya."
read -p "Tekan [Enter] setelah Anda selesai mengedit file .env..."
# ----------------------------------------------------------------

# 7. Jalankan migrasi database
# Opsi --force diperlukan untuk berjalan di mode produksi tanpa konfirmasi
echo "Menjalankan migrasi database..."
php artisan migrate --force

# 8. Optimasi untuk produksi
echo "Melakukan optimasi cache (config, route, view)..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 9. Atur kepemilikan dan izin file/folder
# Ini penting agar web server bisa menulis ke folder storage dan cache
echo "Mengatur kepemilikan folder storage dan bootstrap/cache..."
chown -R $WEB_USER:$WEB_GROUP storage bootstrap/cache
echo "Mengatur izin folder storage dan bootstrap/cache..."
chmod -R 775 storage bootstrap/cache

echo "----------------------------------------------------------------"
echo "Deployment selesai dengan sukses!"
echo "Proyek Anda sekarang ada di: $PROJECT_PATH"
echo "Pastikan konfigurasi web server (Nginx/Apache) Anda sudah menunjuk ke $PROJECT_PATH/public"
echo "----------------------------------------------------------------"
