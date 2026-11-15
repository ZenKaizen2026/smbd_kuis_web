<?php
declare(strict_types=1);

return [
    [
        'id' => 'MOD5_Q01',
        'type' => 'mcq',
        'question' => 'Pada trigger tg_produk_dijual (AFTER INSERT ON ItemNota), pseudorow manakah yang digunakan untuk mengambil jumlah barang (Banyak) yang baru saja dibeli?',
        'code_snippet' => null,
        'options' => [
            'A' => 'OLD.Banyak',
            'B' => 'NEW.Banyak',
            'C' => 'CURRENT.Banyak',
            'D' => 'BEFORE.Banyak',
        ],
        'correct_key' => 'B',
        'explanation' => 'Pada event INSERT, hanya pseudorow NEW yang tersedia karena belum ada baris sebelumnya (OLD tidak tersedia pada INSERT).',
    ],
    [
        'id' => 'MOD5_Q02',
        'type' => 'output_trace',
        'question' => 'Stok awal P001 adalah 97. Trigger tg_produk_dijual mengurangi Stok sebesar NEW.Banyak setelah INSERT ItemNota, sedangkan tg_batal_jual menambah Stok sebesar OLD.Banyak setelah DELETE ItemNota. Jika ditambahkan 3 unit lalu dibatalkan (DELETE), berapa stok akhirnya?',
        'code_snippet' => "-- Awal: Stok P001 = 97\nINSERT INTO ItemNota (NoNota, KodeProduk, Banyak) VALUES ('N001', 'P001', 3);\nDELETE FROM ItemNota WHERE NoNota = 'N001' AND KodeProduk = 'P001';",
        'options' => [
            'A' => '94',
            'B' => '97',
            'C' => '100',
            'D' => '91',
        ],
        'correct_key' => 'B',
        'explanation' => 'INSERT memicu tg_produk_dijual (97 - 3 = 94). DELETE memicu tg_batal_jual menggunakan OLD.Banyak = 3 (94 + 3 = 97).',
    ],
    [
        'id' => 'MOD5_Q03',
        'type' => 'mcq',
        'question' => 'Pada trigger UPDATE seperti tg_update_kasir (AFTER UPDATE ON Kasir), pseudorow apa saja yang dapat diakses di dalam badan trigger?',
        'code_snippet' => null,
        'options' => [
            'A' => 'Hanya NEW',
            'B' => 'Hanya OLD',
            'C' => 'Keduanya: OLD (nilai sebelum diubah) dan NEW (nilai sesudah diubah)',
            'D' => 'Tidak ada, harus memakai SELECT ke tabel Kasir',
        ],
        'correct_key' => 'C',
        'explanation' => 'Event UPDATE menyediakan OLD.<kolom> untuk nilai lama sebelum update dan NEW.<kolom> untuk nilai baru setelah update, sehingga sangat ideal untuk mencatat audit log ke LogKasir.',
    ],
    [
        'id' => 'MOD5_Q04',
        'type' => 'syntax_debug',
        'question' => 'Mengapa trigger BEFORE INSERT berikut gagal dibuat atau tidak mengubah nilai NEW.Stok jika ditulis AFTER INSERT?',
        'code_snippet' => "CREATE TRIGGER tg_cek_stok\nAFTER INSERT ON Produk\nFOR EACH ROW\nBEGIN\n  IF NEW.Stok < 0 THEN SET NEW.Stok = 0; END IF;\nEND;",
        'options' => [
            'A' => 'Karena SET NEW.<kolom> hanya diperbolehkan pada trigger BEFORE, bukan AFTER',
            'B' => 'Karena FOR EACH ROW tidak berlaku untuk tabel Produk',
            'C' => 'Karena tipe data Stok harus VARCHAR',
            'D' => 'Karena trigger tidak boleh memakai blok BEGIN ... END',
        ],
        'correct_key' => 'A',
        'explanation' => 'Pada trigger AFTER, baris sudah ditulis ke tabel sehingga modifikasi nilai NEW.<kolom> dilarang oleh MySQL (error: Updating of NEW row is not allowed in after trigger).',
    ],
    [
        'id' => 'MOD5_Q05',
        'type' => 'mcq',
        'question' => 'Kapan penggunaan Trigger paling tepat dibandingkan Stored Procedure biasa?',
        'code_snippet' => null,
        'options' => [
            'A' => 'Saat ingin menampilkan laporan PDF ke pengguna',
            'B' => 'Saat aturan bisnis (sinkronisasi stok atau pencatatan LogKasir) wajib berjalan otomatis setiap kali DML INSERT/UPDATE/DELETE terjadi tanpa perlu dipanggil manual',
            'C' => 'Saat ingin menggantikan fungsi Primary Key',
            'D' => 'Saat melakukan migrasi database antar server',
        ],
        'correct_key' => 'B',
        'explanation' => 'Trigger berjalan secara otomatis (event-driven) oleh DBMS setiap kali event DML terjadi pada tabel target, menjamin konsistensi stok dan audit trail secara terpusat.',
    ],
];
