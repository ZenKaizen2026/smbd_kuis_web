# Technology Stack

- **Backend**: PHP 8.2 (XAMPP) — Server-Rendered, zero third-party Composer dependencies
- **Database**: MariaDB / MySQL 8 (XAMPP) via PDO (`EMULATE_PREPARES = false`)
  - `db_smbd_kuis` (Aplikasi: users, progress, quiz attempts, custom modules)
  - `db_penjualan_lab` (Sandbox praktikum terisolasi)
- **Frontend**: HTML5 + Precompiled Offline Tailwind CSS (`public/assets/css/tailwind.min.css`)
- **Testing**: Standalone PHP CLI test runner (`php tests/run_tests.php`) + `php -l`
- **Web Server**: XAMPP Apache (`http://localhost/smbd_kuis_web/`)
