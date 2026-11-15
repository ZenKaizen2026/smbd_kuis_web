<?php
declare(strict_types=1);

return [
    [
        'id' => 'MOD1_Q01',
        'type' => 'mcq',
        'question' => 'Manakah karakteristik utama sistem OLTP (Online Transaction Processing) seperti aplikasi kasir db_penjualan?',
        'code_snippet' => null,
        'options' => [
            'A' => 'Data bersifat historis jangka panjang dan jarang diubah (read-heavy)',
            'B' => 'Menangani transaksi harian (INSERT/UPDATE/DELETE) bervolume tinggi dengan skema ternormalisasi',
            'C' => 'Menggunakan skema bintang (Star Schema) untuk agregasi laporan eksekutif',
            'D' => 'Tidak memerlukan primary key maupun foreign key agar proses cepat',
        ],
        'correct_key' => 'B',
        'explanation' => 'OLTP berfokus pada pemrosesan transaksi operasional real-time (catat nota, potong stok, update kasir) sehingga skema dirancang ternormalisasi (3NF) agar terhindar dari anomali insert, update, dan delete.',
    ],
    [
        'id' => 'MOD1_Q02',
        'type' => 'mcq',
        'question' => 'Sebuah tabel nota menyimpan daftar barang dalam satu kolom "DaftarBarang = Pulpen, Buku, Penggaris". Bentuk normal apakah yang dilanggar dan bagaimana memperbaikinya?',
        'code_snippet' => "UNF: Nota(NoNota, TglNota, DaftarBarang)\n-- DaftarBarang berisi nilai majemuk dipisah koma",
        'options' => [
            'A' => 'Melanggar 1NF karena tidak atomik; perbaiki dengan memecah ke tabel detail ItemNota per baris item',
            'B' => 'Melanggar 2NF karena ketergantungan parsial; cukup tambah kolom TotalBayar',
            'C' => 'Melanggar 3NF karena ketergantungan transitif; pindahkan TglNota ke tabel Customer',
            'D' => 'Sudah memenuhi 3NF karena semua data berada dalam satu tabel yang ringkas',
        ],
        'correct_key' => 'A',
        'explanation' => 'Syarat 1NF (First Normal Form) adalah setiap atribut bernilai atomik (tunggal, tidak multivalued/repeating group). Nilai majemuk pada DaftarBarang harus dipecah menjadi baris-baris terpisah di tabel ItemNota.',
    ],
    [
        'id' => 'MOD1_Q03',
        'type' => 'output_trace',
        'question' => 'Perhatikan skema ItemNota(NoNota, KodeProduk, NamaProduk, Banyak) dengan PK komposit (NoNota, KodeProduk). Mengapa skema tersebut belum memenuhi 2NF?',
        'code_snippet' => "PK = (NoNota, KodeProduk)\nFDs:\n  (NoNota, KodeProduk) -> Banyak\n  KodeProduk -> NamaProduk",
        'options' => [
            'A' => 'Karena Banyak bergantung pada NoNota dan KodeProduk sekaligus',
            'B' => 'Karena NamaProduk hanya bergantung sebagian (partial dependency) pada KodeProduk, bukan seluruh PK',
            'C' => 'Karena NoNota bertipe VARCHAR bukan INT AUTO_INCREMENT',
            'D' => 'Karena ada atribut bernilai NULL pada kolom Banyak',
        ],
        'correct_key' => 'B',
        'explanation' => 'Pada 2NF, tidak boleh ada ketergantungan fungsional parsial terhadap sebagian composite primary key. NamaProduk hanya bergantung pada KodeProduk, sehingga harus dipindahkan ke tabel Produk.',
    ],
    [
        'id' => 'MOD1_Q04',
        'type' => 'mcq',
        'question' => 'Pada tabel Nota(NoNota, TglNota, KodeCustomer, NamaCustomer, AlamatCustomer), relasi KodeCustomer -> NamaCustomer -> AlamatCustomer merupakan contoh pelanggaran bentuk normal ke berapa?',
        'code_snippet' => null,
        'options' => [
            'A' => '1NF (Repeating Group)',
            'B' => '2NF (Partial Dependency)',
            'C' => '3NF (Transitive Dependency)',
            'D' => 'BCNF (Multi-valued Dependency)',
        ],
        'correct_key' => 'C',
        'explanation' => 'Atribut non-kunci (NamaCustomer, AlamatCustomer) bergantung pada atribut non-kunci lain (KodeCustomer), bukan langsung pada Primary Key NoNota. Ini disebut Transitive Dependency (melanggar 3NF) dan diselesaikan dengan memisahkan tabel Customer.',
    ],
    [
        'id' => 'MOD1_Q05',
        'type' => 'mcq',
        'question' => 'Urutan tahapan Database Life Cycle (DBLC) yang tepat sebelum implementasi fisik di MySQL adalah...',
        'code_snippet' => null,
        'options' => [
            'A' => 'Physical Design -> Logical Design -> Conceptual Design -> Requirements',
            'B' => 'Requirements & Analysis -> Conceptual Design (ERD) -> Logical Design (Normalisasi) -> Physical Design',
            'C' => 'Testing & Maintenance -> Conceptual Design -> Physical Design',
            'D' => 'Normalisasi 3NF -> Wawancara User -> Membuat Trigger',
        ],
        'correct_key' => 'B',
        'explanation' => 'Siklus perancangan basis data dimulai dari pengumpulan kebutuhan (Requirements), pemodelan konseptual (ERD), pemetaan logis & normalisasi (1NF-3NF), lalu perancangan fisik (tipe data, constraint, index di DBMS).',
    ],
];
