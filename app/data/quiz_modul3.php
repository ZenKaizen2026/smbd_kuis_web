<?php
declare(strict_types=1);

return [
    [
        'id' => 'MOD3_Q01',
        'type' => 'mcq',
        'question' => 'Apa perbedaan utama antara INNER JOIN dan LEFT JOIN pada query Customer c JOIN Nota n ON c.KodeCustomer = n.KodeCustomer?',
        'code_snippet' => null,
        'options' => [
            'A' => 'INNER JOIN menampilkan semua Customer termasuk yang belum pernah bertransaksi, sedangkan LEFT JOIN tidak',
            'B' => 'LEFT JOIN tetap menampilkan seluruh Customer meskipun belum punya baris di Nota (kolom Nota bernilai NULL)',
            'C' => 'LEFT JOIN hanya mengembalikan 1 baris per Customer tanpa duplikasi Nota',
            'D' => 'Tidak ada perbedaan selama kedua tabel memiliki Primary Key',
        ],
        'correct_key' => 'B',
        'explanation' => 'LEFT JOIN mempertahankan semua baris dari tabel kiri (Customer) walaupun tidak memiliki pasangan di tabel kanan (Nota), dengan mengisi kolom dari Nota sebagai NULL.',
    ],
    [
        'id' => 'MOD3_Q02',
        'type' => 'output_trace',
        'question' => 'Pada query di bawah, apa fungsi huruf "n" dan "i" setelah nama tabel, serta kata kunci "AS" pada kolom?',
        'code_snippet' => "SELECT n.NoNota AS Nomor_Nota, SUM(i.Banyak) AS Total_Item\nFROM Nota n\nINNER JOIN ItemNota i ON n.NoNota = i.NoNota\nGROUP BY n.NoNota;",
        'options' => [
            'A' => 'n dan i adalah nama database sementara; AS wajib untuk membuat VIEW',
            'B' => 'n dan i adalah Table Alias untuk mempersingkat referensi kolom; AS memberi Column Alias pada header hasil',
            'C' => 'n dan i adalah variabel lokal stored procedure; AS mengonversi tipe data ke STRING',
            'D' => 'n dan i adalah indeks tabel; AS mengurutkan hasil secara ascending',
        ],
        'correct_key' => 'B',
        'explanation' => 'Table alias (n, i) mempermudah penulisan kolom yang berpotensi ambigu saat JOIN, sedangkan AS mendefinisikan nama label (header) yang lebih informatif pada result set.',
    ],
    [
        'id' => 'MOD3_Q03',
        'type' => 'mcq',
        'question' => 'Manakah pernyataan yang tepat mengenai DATABASE VIEW (seperti ViewDetailTransaksi di db_penjualan_lab)?',
        'code_snippet' => null,
        'options' => [
            'A' => 'VIEW menyalin dan menduplikasi seluruh data fisik ke disk baru setiap kali dibuat',
            'B' => 'VIEW adalah tabel virtual hasil query SELECT tersimpan yang selalu membaca data terbaru dari tabel dasarnya',
            'C' => 'VIEW tidak boleh mengandung operasi JOIN maupun fungsi agregat SUM',
            'D' => 'Sekali VIEW dibuat, tabel aslinya otomatis terhapus',
        ],
        'correct_key' => 'B',
        'explanation' => 'VIEW menyimpan definisi query SELECT (virtual table), bukan salinan data terpisah, sehingga sangat berguna menyederhanakan query JOIN/agregasi kompleks dan membatasi akses kolom.',
    ],
    [
        'id' => 'MOD3_Q04',
        'type' => 'output_trace',
        'question' => 'Apa nilai yang dikembalikan oleh ekspresi kontrol alur berikut jika kolom NoHP milik Customer bernilai NULL dan BonusPoint bernilai 120?',
        'code_snippet' => "SELECT IFNULL(NoHP, 'Belum Ada') AS Kontak,\n       CASE WHEN BonusPoint >= 100 THEN 'Gold' ELSE 'Reguler' END AS Tier\nFROM Customer;",
        'options' => [
            'A' => "Kontak = NULL, Tier = 'Reguler'",
            'B' => "Kontak = 'Belum Ada', Tier = 'Gold'",
            'C' => "Kontak = '0', Tier = 'Gold'",
            'D' => "Error karena IFNULL tidak bisa digabung dengan CASE",
        ],
        'correct_key' => 'B',
        'explanation' => "IFNULL(NoHP, 'Belum Ada') mengembalikan argumen kedua saat NoHP bernilai NULL. Karena BonusPoint (120) >= 100, CASE mengembalikan 'Gold'.",
    ],
    [
        'id' => 'MOD3_Q05',
        'type' => 'syntax_debug',
        'question' => 'Mengapa query agregasi berikut mengalami error di MySQL mode ONLY_FULL_GROUP_BY?',
        'code_snippet' => "SELECT NoNota, KodeProduk, SUM(Banyak) AS Total\nFROM ItemNota\nGROUP BY NoNota;",
        'options' => [
            'A' => 'Karena SUM(Banyak) tidak boleh diberi alias Total',
            'B' => 'Karena kolom KodeProduk ada di SELECT tetapi tidak termasuk dalam GROUP BY maupun fungsi agregat',
            'C' => 'Karena GROUP BY harus ditulis sebelum FROM',
            'D' => 'Karena ItemNota tidak boleh menggunakan SUM tanpa HAVING',
        ],
        'correct_key' => 'B',
        'explanation' => 'Setiap kolom non-agregat di klausa SELECT harus tercantum pada klausa GROUP BY agar hasil pengelompokan deterministik.',
    ],
];
