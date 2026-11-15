<?php
declare(strict_types=1);

/**
 * Katalog resmi 5 Modul Pembelajaran SMBDL (SI167) mengacu pada docs/PRD.md.
 */
return [
    1 => [
        'no'      => 1,
        'title'   => 'Konsep Aplikasi DB & Normalisasi',
        'summary' => 'OLTP vs OLAP, Siklus Hidup Database, dan Normalisasi tabel tidak normal (UNF -> 1NF -> 2NF -> 3NF) pada studi kasus db_penjualan.',
        'topics'  => ['OLTP vs OLAP', 'UNF ke 1NF', 'Partial Dependency (2NF)', 'Transitive & Turunan (3NF)'],
    ],
    2 => [
        'no'      => 2,
        'title'   => 'Physical Design & Batasan Integritas',
        'summary' => 'Tipe data DBMS, 5 Integrity Constraints (Required, Domain, Entity, Referential, General), DDL/DML, dan Select sederhana.',
        'topics'  => ['Primary & Foreign Key', 'UNIQUE & DEFAULT', 'ON UPDATE CASCADE', 'Uji Pelanggaran Constraint'],
    ],
    3 => [
        'no'      => 3,
        'title'   => 'JOIN, VIEW, Control Flow & Alias',
        'summary' => 'INNER/LEFT/RIGHT JOIN, Aggregate & Subquery, CREATE VIEW, CASE/IF/IFNULL/NULLIF, serta arti alias tabel (t1, n, i) dan header (AS).',
        'topics'  => ['INNER & LEFT JOIN', 'Table Alias & AS Header', 'VIEW Virtual Table', 'CASE / IF / IFNULL / NULLIF'],
    ],
    4 => [
        'no'      => 4,
        'title'   => 'UDF, Stored Procedure & Cursor',
        'summary' => 'Perbedaan User Defined Function (SELECT fc...()) vs Stored Procedure (CALL proc...()) serta mekanisme iterasi CURSOR (DECLARE, OPEN, FETCH, CLOSE).',
        'topics'  => ['SELECT fcCekStok()', 'CALL TambahNotaBaru()', 'Cursor Loop & Handler', 'ProcBonusPoint'],
    ],
    5 => [
        'no'      => 5,
        'title'   => 'Database Trigger (BEFORE/AFTER)',
        'summary' => 'Otomasi stok Produk & audit LogKasir menggunakan Trigger INSERT/UPDATE/DELETE dengan pseudorow NEW dan OLD pada db_penjualan_lab.',
        'topics'  => ['NEW vs OLD', 'tg_produk_dijual', 'tg_batal_jual', 'tg_update_kasir'],
    ],
];
