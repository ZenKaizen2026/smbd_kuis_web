# Panduan Deployment — SMBDL Quiz & Simulation Web

Aplikasi ini menggunakan **PHP 8.2 Native** + **MySQL / MariaDB** tanpa dependensi runtime Node.js atau Composer, dengan dua database terpisah:
1. `db_smbd_kuis` (data pengguna, progres belajar, riwayat kuis, catatan & modul ekstra)
2. `db_penjualan_lab` (database sandbox praktikum yang bisa di-reset via `SnapshotResetter`)

---

## Opsi A: Deployment Cepat dengan Docker Compose

Cocok untuk VPS, server lokal kampus, atau cloud VM dengan Docker.

1. **Salin dan sesuaikan `.env`** (opsional, untuk mengganti password root DB):
   ```bash
   cp .env.example .env
   ```
   Isi `DB_PASS` dengan password yang kuat.

2. **Build & jalankan container**:
   ```bash
   docker compose up -d --build
   ```

3. **Inisialisasi skema, akun demo, dan database sandbox**:
   ```bash
   docker compose exec web php sql/install_and_seed.php
   ```

4. **Jalankan test suite verifikasi**:
   ```bash
   docker compose exec web php tests/run_tests.php
   ```

5. Akses aplikasi di `http://<IP-SERVER>:8080/`.

---

## Opsi B: Deployment di VPS Linux (Apache + PHP 8.2 + MariaDB)

1. **Install paket yang dibutuhkan** (Ubuntu/Debian):
   ```bash
   sudo apt update
   sudo apt install -y apache2 php8.2 php8.2-mysql php8.2-mbstring mariadb-server poppler-utils
   sudo a2enmod rewrite headers
   ```
   > `poppler-utils` menyediakan `pdftotext` untuk fitur ekstraksi teks saat admin mengunggah modul `.pdf`.

2. **Clone repo & atur permission `storage/`**:
   ```bash
   git clone https://github.com/ZenKaizen2026/smbd_kuis_web.git /var/www/smbd_kuis_web
   cd /var/www/smbd_kuis_web
   mkdir -p storage/logs storage/uploads
   sudo chown -R www-data:www-data storage
   sudo chmod -R 775 storage
   ```

3. **Konfigurasi `.env`**:
   ```bash
   cp .env.example .env
   nano .env
   ```
   Pastikan user database di `.env` memiliki hak akses `CREATE`, `DROP`, `TRIGGER`, `CREATE ROUTINE`, dan `ALTER ROUTINE` untuk `db_smbd_kuis` serta `db_penjualan_lab` (dibutuhkan oleh `SnapshotResetter`).

4. **Aktifkan `log_bin_trust_function_creators`** di MariaDB/MySQL (agar UDF & Trigger dapat dibuat tanpa hak `SUPER`):
   ```sql
   SET GLOBAL log_bin_trust_function_creators = 1;
   ```
   Tambahkan `log_bin_trust_function_creators = 1` di `/etc/mysql/mariadb.conf.d/50-server.cnf` agar permanen.

5. **Inisialisasi database & uji**:
   ```bash
   php sql/install_and_seed.php
   php tests/run_tests.php
   ```

6. **Konfigurasi VirtualHost Apache** mengarah ke folder `public/`:
   ```apache
   <VirtualHost *:80>
       ServerName smbdl.domainanda.com
       DocumentRoot /var/www/smbd_kuis_web/public

       <Directory /var/www/smbd_kuis_web/public>
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```
   Aktifkan HTTPS dengan Certbot (`sudo certbot --apache -d smbdl.domainanda.com`).

---

## Opsi C: Shared Hosting / cPanel

1. Upload seluruh isi proyek ke folder di luar `public_html` (mis. `~/smbd_kuis_web`), lalu arahkan subdomain/DocumentRoot ke `~/smbd_kuis_web/public` (atau salin isi `public/` ke `public_html` dan sesuaikan `dirname(__DIR__)` pada `public/*.php`).
2. Buat `.env` berisi kredensial database hosting. Jika shared hosting membatasi pembuatan database baru oleh user, buat `db_smbd_kuis` dan `db_penjualan_lab` terlebih dahulu lewat panel cPanel, lalu import `sql/db_smbd_kuis.sql`, `sql/db_penjualan_lab_tables.sql`, dan `sql/db_penjualan_lab_seed.sql`.
3. Pastikan `storage/logs` dan `storage/uploads` writable (`755`/`775`).

---

## Checklist Keamanan Pasca-Deploy

- [ ] `.env` tidak pernah di-commit ke Git.
- [ ] `DocumentRoot` mengarah ke `public/` sehingga `app/`, `sql/`, `storage/`, dan `.env` tidak dapat diakses via URL.
- [ ] `display_errors` bernilai `Off` di production (sudah diatur di `public/.htaccess` dan `Dockerfile`).
- [ ] Ganti password akun `mahasiswa_demo` atau batasi akses admin jika dipakai untuk kelas live.
