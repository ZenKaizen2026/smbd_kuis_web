<?php
declare(strict_types=1);

return [
    [
        'id' => 'MOD4_Q01',
        'type' => 'mcq',
        'question' => 'Bagaimana cara memanggil User-Defined Function (UDF) fcCekStok di MySQL dibandingkan dengan Stored Procedure ProcBonusPoint?',
        'code_snippet' => null,
        'options' => [
            'A' => 'CALL fcCekStok(\'P001\'); dan SELECT ProcBonusPoint();',
            'B' => 'SELECT fcCekStok(\'P001\'); dan CALL ProcBonusPoint();',
            'C' => 'EXEC fcCekStok(\'P001\'); dan RUN ProcBonusPoint();',
            'D' => 'Keduanya wajib dipanggil menggunakan kata kunci CALL',
        ],
        'correct_key' => 'B',
        'explanation' => 'User-Defined Function (UDF) mengembalikan tepat satu nilai lewat RETURN sehingga dipanggil dalam ekspresi SQL seperti SELECT fcCekStok(\'P001\'). Stored Procedure dieksekusi dengan perintah CALL.',
    ],
    [
        'id' => 'MOD4_Q02',
        'type' => 'output_trace',
        'question' => 'Urutan siklus hidup CURSOR yang benar di dalam sebuah Stored Procedure MySQL (seperti ProcBonusPoint / ProcDaftarNoHP) adalah...',
        'code_snippet' => null,
        'options' => [
            'A' => 'OPEN -> DECLARE -> FETCH -> CLOSE',
            'B' => 'DECLARE -> OPEN -> FETCH (loop) -> CLOSE',
            'C' => 'FETCH -> OPEN -> DECLARE -> CLOSE',
            'D' => 'DECLARE -> FETCH -> OPEN -> COMMIT',
        ],
        'correct_key' => 'B',
        'explanation' => 'Cursor harus dideklarasikan terlebih dahulu (DECLARE cursor + CONTINUE HANDLER FOR NOT FOUND), dibuka (OPEN), dibaca baris demi baris dalam loop (FETCH), lalu ditutup (CLOSE).',
    ],
    [
        'id' => 'MOD4_Q03',
        'type' => 'syntax_debug',
        'question' => 'Mengapa loop CURSOR berikut bisa mengalami infinite loop (tidak pernah berhenti) saat semua baris telah dibaca?',
        'code_snippet' => "DECLARE done INT DEFAULT 0;\nDECLARE cur_cust CURSOR FOR SELECT KodeCustomer FROM Customer;\n-- Handler belum didefinisikan\nOPEN cur_cust;\nread_loop: LOOP\n  FETCH cur_cust INTO v_kode;\n  IF done = 1 THEN LEAVE read_loop; END IF;\nEND LOOP;",
        'options' => [
            'A' => 'Karena variabel done bertipe INT bukan BOOLEAN',
            'B' => 'Karena tidak ada DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;',
            'C' => 'Karena perintah OPEN cur_cust harus berada di dalam LOOP',
            'D' => 'Karena LEAVE read_loop harus diganti dengan RETURN 0',
        ],
        'correct_key' => 'B',
        'explanation' => 'Tanpa CONTINUE HANDLER FOR NOT FOUND SET done = 1;, kondisi done tidak pernah berubah menjadi 1 ketika FETCH mencapai akhir result set.',
    ],
    [
        'id' => 'MOD4_Q04',
        'type' => 'mcq',
        'question' => 'Manakah perbedaan mendasar antara Stored Function (UDF) dan Stored Procedure di MySQL?',
        'code_snippet' => null,
        'options' => [
            'A' => 'Stored Function dapat mengembalikan banyak result set sekaligus, sedangkan Stored Procedure tidak bisa',
            'B' => 'Stored Function wajib mengembalikan 1 nilai via RETURN dan bisa dipakai di klausa SELECT/WHERE, sedangkan Stored Procedure tidak',
            'C' => 'Stored Procedure tidak dapat menerima parameter IN/OUT',
            'D' => 'Stored Function tidak membutuhkan DELIMITER saat pembuatan',
        ],
        'correct_key' => 'B',
        'explanation' => 'Stored Function selalu memiliki RETURN <nilai> dan dapat disematkan di query SELECT/WHERE. Stored Procedure lebih fleksibel untuk logika prosedural, DML massal, parameter IN/OUT/INOUT, dan mengembalikan result set.',
    ],
    [
        'id' => 'MOD4_Q05',
        'type' => 'output_trace',
        'question' => 'Jika fcCekStok(\'P001\') mengembalikan status \'Tersedia\' ketika Stok > 10 dan \'Menipis\' ketika Stok <= 10, apa keluaran SELECT fcCekStok(\'P001\') setelah stok P001 bernilai 97?',
        'code_snippet' => null,
        'options' => [
            'A' => '\'Menipis\'',
            'B' => '\'Tersedia\'',
            'C' => '97',
            'D' => 'NULL',
        ],
        'correct_key' => 'B',
        'explanation' => 'Karena stok P001 = 97 (> 10), percabangan IF di dalam UDF fcCekStok menghasilkan string \'Tersedia\'.',
    ],
];
