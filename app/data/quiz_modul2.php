<?php
declare(strict_types=1);

return [
    [
        'id' => 'MOD2_Q01',
        'type' => 'mcq',
        'question' => 'Batasan integritas (Integrity Constraint) yang menjamin bahwa nilai Foreign Key pada tabel ItemNota harus ada pada Primary Key tabel Produk atau Nota disebut...',
        'code_snippet' => null,
        'options' => [
            'A' => 'Entity Integrity',
            'B' => 'Referential Integrity',
            'C' => 'Domain Integrity',
            'D' => 'User-Defined Integrity',
        ],
        'correct_key' => 'B',
        'explanation' => 'Referential Integrity menjaga konsistensi antar tabel berelasi melalui FOREIGN KEY sehingga child table (ItemNota) tidak dapat mereferensikan parent key yang tidak ada di Produk atau Nota.',
    ],
    [
        'id' => 'MOD2_Q02',
        'type' => 'syntax_debug',
        'question' => 'Perhatikan perintah DDL berikut. Mengapa perintah INSERT di bawahnya akan ditolak oleh MySQL?',
        'code_snippet' => "CREATE TABLE Produk (\n  KodeProduk CHAR(4) PRIMARY KEY,\n  NamaProduk VARCHAR(50) NOT NULL UNIQUE,\n  Harga INT NOT NULL CHECK (Harga > 0),\n  Stok INT NOT NULL DEFAULT 0\n);\nINSERT INTO Produk (KodeProduk, NamaProduk, Harga, Stok) VALUES ('P010', 'Pulpen Gel', 0, 10);",
        'options' => [
            'A' => 'Melanggar UNIQUE constraint pada NamaProduk',
            'B' => 'Melanggar Entity Integrity karena KodeProduk bertipe CHAR(4)',
            'C' => 'Melanggar Domain/CHECK constraint karena Harga bernilai 0 (harus > 0)',
            'D' => 'Melanggar NOT NULL karena kolom Stok tidak boleh bernilai 10',
        ],
        'correct_key' => 'C',
        'explanation' => 'Kolom Harga memiliki constraint CHECK (Harga > 0). Memasukkan nilai 0 melanggar batasan domain nilai tersebut sehingga ditolak oleh MySQL.',
    ],
    [
        'id' => 'MOD2_Q03',
        'type' => 'mcq',
        'question' => 'Pada db_penjualan_lab, kolom NamaProduk diberi constraint UNIQUE. Apa akibatnya jika kita menjalankan INSERT produk baru dengan KodeProduk berbeda namun NamaProduk sama persis dengan yang sudah ada?',
        'code_snippet' => null,
        'options' => [
            'A' => 'MySQL otomatis mengganti NamaProduk lama dengan yang baru',
            'B' => 'Query berhasil dengan warning duplikat',
            'C' => 'MySQL menolak INSERT dengan error Duplicate entry for key',
            'D' => 'Stok produk lama otomatis ditambahkan dengan stok baru',
        ],
        'correct_key' => 'C',
        'explanation' => 'UNIQUE constraint memastikan tidak ada dua baris yang memiliki nilai sama pada kolom tersebut, meskipun Primary Key (KodeProduk) berbeda.',
    ],
    [
        'id' => 'MOD2_Q04',
        'type' => 'syntax_debug',
        'question' => 'Manakah sintaks DML yang benar untuk menaikkan Harga semua produk kategori alat tulis sebesar 10% di tabel Produk?',
        'code_snippet' => null,
        'options' => [
            'A' => 'MODIFY Produk SET Harga = Harga * 1.1;',
            'B' => 'UPDATE Produk SET Harga = Harga * 1.1 WHERE KodeProduk IS NOT NULL;',
            'C' => 'ALTER TABLE Produk SET Harga = Harga * 1.1;',
            'D' => 'INSERT INTO Produk Harga VALUES (Harga * 1.1);',
        ],
        'correct_key' => 'B',
        'explanation' => 'Perubahan nilai baris data menggunakan DML UPDATE <tabel> SET <kolom> = <ekspresi> WHERE <kondisi>. ALTER TABLE adalah DDL untuk mengubah struktur tabel, bukan isi baris.',
    ],
    [
        'id' => 'MOD2_Q05',
        'type' => 'output_trace',
        'question' => 'Apa hasil dari query SELECT berikut jika tabel Produk berisi P001 (Stok=100), P002 (Stok=50), P003 (Stok=25)?',
        'code_snippet' => "SELECT KodeProduk, Stok FROM Produk WHERE Stok >= 50 ORDER BY Stok ASC;",
        'options' => [
            'A' => 'P001 (100), P002 (50)',
            'B' => 'P002 (50), P001 (100)',
            'C' => 'P003 (25), P002 (50), P001 (100)',
            'D' => 'Hanya P001 (100)',
        ],
        'correct_key' => 'B',
        'explanation' => 'WHERE Stok >= 50 menyaring P001 (100) dan P002 (50). ORDER BY Stok ASC mengurutkan dari nilai terkecil ke terbesar sehingga P002 (50) muncul lebih dulu sebelum P001 (100).',
    ],
];
