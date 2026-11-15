# PRD, Algoritma, Flowchart & Design Rules — SMBDL Quiz & Simulation Web (MVP)

> **Dokumen Backup Memori & Pedoman Pengembangan**
> Status: **Fase 0 Final (Siap Fase 1)** — Seluruh temuan Stage 5–9 & `Penting.txt` telah diintegrasikan.
> Sumber Kebenaran Tunggal Materi: Modul 1–5 SI167, `Laporan_Praktikum_smbdl.docx`, dan `db_penjualan.sql`.

---

## 1. System Instruction & Rule Kerja (Anti-Halusinasi)

1. **Single Source of Truth**: Semua materi, soal kuis, contoh query, dan simulasi **wajib** mengacu pada file referensi di root workspace `d:\smbd_kuis_web\`:
   - `20261015134657_SI167_Modul_SMBD_Lanjut_1-2.pdf` (Modul 1 & 2) — *di-ignore Git (`*.pdf`)*
   - `20261016110827_SI167_Modul_SMBD_Lanjut_3.pdf` (Modul 3) — *di-ignore Git (`*.pdf`)*
   - `20261016110919_SI167_Modul_SMBD_Lanjut_4.pdf` (Modul 4) — *di-ignore Git (`*.pdf`)*
   - `20261106121648_SI167_MODUL_SMBD_Lanjut_5.pdf` (Modul 5) — *di-ignore Git (`*.pdf`)*
   - `Laporan_Praktikum_smbdl.docx` & `db_penjualan.sql` (Implementasi final; `db_penjualan.sql` dipecah menjadi file DDL, Seed, dan Routines di `sql/` dengan menghapus `DEFINER=root@localhost`).
2. **Modular Step-by-Step**: Tidak melompat ke step berikutnya sebelum step aktif diuji, dicek kodingannya, dan di-approve oleh user.
3. **Wajib Verifikasi Setiap Perubahan**: Cek sintaks (`php -l`), keamanan (parameterized queries, CSRF, output escaping, session guard), dan jalankan `php tests/run_tests.php`.
4. **Memory Anchor**: Jika percakapan panjang, baca ulang `docs/PRD.md` dan konfirmasikan status step terakhir kepada user.

---

## 2. Keputusan Arsitektur Terkunci & Pemetaan 5 Modul

| Aspek | Keputusan | Konsekuensi Teknis |
| :--- | :--- | :--- |
| **Stack Web (1A)** | **PHP 8.2 Server-Rendered + Tailwind CSS** (precompiled offline `public/assets/css/tailwind.min.css`) | Nol bundler/Node di runtime; arsitektur `public/` (controller) + `app/views/` (template) dengan pola POST-Redirect-GET (`$_SESSION['flash']`). |
| **Isolasi DB (2A)** | **2 Database**: `db_smbd_kuis` (aplikasi) & `db_penjualan_lab` (sandbox) | **Reset Snapshot** mengeksekusi ulang: (1) `sql/db_penjualan_lab_tables.sql` (DROP & CREATE tables), (2) `sql/db_penjualan_lab_seed.sql` (INSERT data awal), (3) `sql/db_penjualan_lab_routines.php` (array statement `CREATE VIEW/FUNCTION/PROCEDURE/TRIGGER` satu per satu via PDO **tanpa `DELIMITER $$`** dan **tanpa `DEFINER`**). Urutan ini mencegah trigger memotong stok 2x atau mengotori `LogKasir`. |
| **Serving & Git (3A)** | **Apache Alias** `/smbd_kuis_web` -> `D:\smbd_kuis_web\public` + `git init` | Semua link/redirect menggunakan path relatif. `.gitignore` mengecualikan `*.pdf`, `Penting.txt`, `.env`, `storage/logs/*.log`, `vendor/`. |
| **Auth Step 1 (4A)** | **Login + Register + 1 Akun Demo Seeder** (`mahasiswa_demo` / `demo1234`) | `password_hash()` / `password_verify()`, `session_regenerate_id(true)`, cookie `HttpOnly` + `SameSite=Lax`, CSRF token. |

### Pemetaan Resmi 5 Modul (`modul_no`: 1–5)
1. **Modul 1 — Konsep Aplikasi DB & Normalisasi**: OLTP vs OLAP, Siklus Hidup DB, Normalisasi `UNF -> 1NF -> 2NF -> 3NF` (fixture statis `app/data/normalisasi_fixtures.php`).
2. **Modul 2 — Physical Design, Batasan Integritas & DDL/DML/Select**: 5 Integrity Constraints, DDL/DML, Select sederhana, uji pelanggaran constraint di `db_penjualan_lab`.
3. **Modul 3 — JOIN, VIEW & Control Flow**: `INNER/LEFT/RIGHT JOIN`, Aggregate, Subquery, `VIEW`, `CASE/IF/IFNULL/NULLIF`, arti table alias (`t1`, `n`, `i`) & header (`AS`).
4. **Modul 4 — UDF, Stored Procedure & Cursor**: Pemanggilan `SELECT fc...()` vs `CALL proc...()`, serta tabel trace iterasi `CURSOR` (`ProcBonusPoint` / `ProcDaftarNoHP`) yang diemulasikan di PHP.
5. **Modul 5 — Database Trigger**: `BEFORE/AFTER` `INSERT/UPDATE/DELETE`, pseudorow `NEW` vs `OLD` pada `ItemNota`/`Kasir` dengan tampilan *Before vs After* pada `Produk` & `LogKasir`.

---

## 3. Spesifikasi Model Data (`sql/db_smbd_kuis.sql`)

```sql
CREATE TABLE app_users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nim_or_username VARCHAR(50) NOT NULL UNIQUE,
  nama_lengkap VARCHAR(100) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE app_learning_progress (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  modul_no TINYINT UNSIGNED NOT NULL,
  status ENUM('belum', 'ulang', 'paham') NOT NULL DEFAULT 'belum',
  last_score DECIMAL(5,2) NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_modul (user_id, modul_no),
  CONSTRAINT fk_prog_user FOREIGN KEY (user_id) REFERENCES app_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE app_quiz_attempts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  modul_no TINYINT UNSIGNED NOT NULL,
  score_percent DECIMAL(5,2) NOT NULL,
  correct_count SMALLINT UNSIGNED NOT NULL,
  total_questions SMALLINT UNSIGNED NOT NULL,
  duration_seconds INT UNSIGNED NOT NULL DEFAULT 0,
  detail_json JSON NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_attempt_user FOREIGN KEY (user_id) REFERENCES app_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Fitur tambahan dari Penting.txt: Knowledge / Materi Tambahan per Modul
CREATE TABLE app_custom_modules (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  modul_no TINYINT UNSIGNED NOT NULL,
  judul VARCHAR(150) NOT NULL,
  isi_materi TEXT NOT NULL,
  contoh_sql TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_custom_mod_user FOREIGN KEY (user_id) REFERENCES app_users(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

*(Domain `db_penjualan_lab`: `Produk`, `Kasir`, `Customer`, `Nota`, `ItemNota`, `LogKasir` — sesuai `db_penjualan.sql`)*.

---

## 4. Kontrak Data Kuis & Simulator Whitelist

### 4.1 Kontrak Bank Soal (`app/data/quiz_modul{1..5}.php`) & `detail_json`
Setiap file mengembalikan array soal dengan kontrak tetap:
```php
[
  [
    'id' => 'MOD1_Q01', // ID permanen
    'type' => 'mcq',    // 'mcq' | 'output_trace' | 'syntax_debug'
    'question' => 'Pertanyaan...',
    'code_snippet' => 'SELECT ...', // opsional (string|null)
    'options' => ['A' => '...', 'B' => '...', 'C' => '...', 'D' => '...'],
    'correct_key' => 'B',
    'explanation' => 'Pembahasan mengacu pada Modul & Laporan...'
  ]
]
```
Struktur `app_quiz_attempts.detail_json`:
```json
{
  "answers": {
    "MOD1_Q01": {"selected": "B", "is_correct": true}
  },
  "score_summary": {"total": 5, "correct": 4}
}
```
Ambang lulus: **`score_percent >= 75.00`** -> status `paham` (rekomendasi **Lanjut**), selain itu `ulang` (rekomendasi **Ulang**).

### 4.2 Katalog Skenario Whitelist `SimulatorSandbox` (`app/services/SimulatorSandbox.php`)
Signature: `SimulatorSandbox::run(string $scenarioId, array $params = []): array`
Return contract: `['ok' => bool, 'sql_executed' => string, 'explanation' => string, 'before' => array, 'after' => array, 'trace' => array|null, 'error' => string|null]`

| `scenario_id` | Modul | Parameter Tervalidasi | `pre_query` / `action_query` / `post_query` |
| :--- | :---: | :--- | :--- |
| `m1_norm_stepper` | 1 | `stage` (`unf\|1nf\|2nf\|3nf`) | Membaca fixture statis `app/data/normalisasi_fixtures.php` |
| `m2_constraint_fk` | 2 | `nonota`, `kode_produk` | Uji `INSERT INTO itemnota` dengan FK tidak valid -> tangkap error integritas |
| `m2_constraint_uq` | 2 | `nama_produk` | Uji `INSERT INTO produk` duplikat `NamaProduk` -> tangkap error UNIQUE |
| `m3_join_inner_left` | 3 | `join_type` (`inner\|left`) | `SELECT` `Customer` JOIN `Nota` + diagram Venn SVG statis + penjelasan alias `c`/`n` & `AS` |
| `m3_view_select` | 3 | `view_name` (whitelist 4 view) | `SELECT * FROM <view>` (`daftarhargaproduk`, `tampiltotalnota`, `viewdetailtransaksi`, `viewtransaksikasir`) |
| `m3_control_flow` | 3 | `func` (`case\|if\|ifnull\|nullif`) | Menjalankan query `CASE`/`IF`/`IFNULL`/`NULLIF` sesuai Modul 3 |
| `m4_udf_cek_stok` | 4 | `kode_produk` (`P001\|P002\|P003`) | `SELECT fcCekStok(:kd) AS cekstok` |
| `m4_sp_bonus_point` | 4 | *(tanpa param)* | Snapshot `Customer` before -> `CALL ProcBonusPoint()` -> after + **emulasi trace loop `CURSOR` di PHP** |
| `m5_tg_insert_item` | 5 | `kode_produk`, `banyak` (1–10) | Snapshot `Produk` before -> `INSERT INTO itemnota` -> snapshot `Produk` after (`tg_produk_dijual`) |
| `m5_tg_update_kasir` | 5 | `kode_kasir`, `pass_baru` | Snapshot `LogKasir` before -> `UPDATE kasir SET Pass=...` -> after (`tg_update_kasir`) |
| `m5_tg_delete_item` | 5 | `nonota`, `kode_produk` | Snapshot `Produk` before -> `DELETE FROM itemnota` -> after (`tg_batal_jual`) |

### 4.3 Fitur Tambahan (`app_custom_modules` dari `Penting.txt`)
Mahasiswa dapat menambah, melihat, dan menghapus **Catatan / Materi Tambahan** per modul (`judul`, `isi_materi`, `contoh_sql` opsional) melalui `public/materi.php` (terproteksi login & CSRF), untuk memperkaya referensi belajar di luar 5 modul utama.

---

## 5. Kontrak Test Runner (`tests/run_tests.php`) & Config

- **Config (`app/config/database.php`)**: Mengembalikan array PHP native dengan fallback default XAMPP (`127.0.0.1`, `root`, `''`) dan opsional membaca `.env` lokal via `parse_ini_file()` tanpa library eksternal.
- **Test Runner (`php tests/run_tests.php`)**: Script CLI standalone menggunakan `assert()` / fungsi helper `test_case()`, keluar dengan exit code `0` (semua lulus) atau `1` (ada yang gagal).
- **Mandatory Step 1 Tests**:
  1. Koneksi PDO ke `db_smbd_kuis` & `db_penjualan_lab`.
  2. Verifikasi akun seeder `mahasiswa_demo` (`password_verify('demo1234', ...)`).
  3. Generate & validasi CSRF token.
  4. Register user uji (dalam transaksi/cleanup) + tolak duplikat username + tolak password salah.

---

## 6. Struktur Folder & Git Workflow

```text
d:\smbd_kuis_web\
├── docs\                   # PRD.md
├── sql\                    # db_smbd_kuis.sql, db_penjualan_lab_tables.sql, db_penjualan_lab_seed.sql, db_penjualan_lab_routines.php
├── app\
│   ├── config\             # database.php
│   ├── helpers\            # auth.php, csrf.php, logger.php, flash.php
│   ├── services\           # QuizEngine.php, SimulatorSandbox.php, SnapshotResetter.php, CustomModuleService.php
│   ├── data\               # normalisasi_fixtures.php, quiz_modul1..5.php
│   └── views\              # layouts/ (header.php, navbar.php, footer.php) & halaman view
├── public\                 # Web root Apache Alias (/smbd_kuis_web)
│   ├── assets\css\         # tailwind.min.css (precompiled offline)
│   └── *.php               # index.php, login.php, register.php, logout.php, dashboard.php, quiz.php, sim.php, materi.php
├── storage\logs\           # app.log (di-ignore Git)
└── tests\                  # run_tests.php
```

- **`.gitignore`**: `*.pdf`, `Penting.txt`, `.env`, `storage/logs/*.log`, `vendor/`.
- **Git Workflow**: `main` <- `feat/step-1-login`, `feat/step-2-dashboard`, dst.
