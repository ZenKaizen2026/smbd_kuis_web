<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

final class SimulatorSandbox
{
    public static function scenarios(): array
    {
        return [
            'm1_norm_stepper' => [
                'id' => 'm1_norm_stepper',
                'modul' => 1,
                'title' => 'Normalisasi 3NF & Inspeksi Tabel Nota',
                'description' => 'Pelajari pemisahan repeating group dan ketergantungan transitif menjadi skema 3NF.',
                'instruction' => 'Ketik query SELECT untuk menampilkan kolom Nonota, TglTran, dan KodeCus dari tabel `nota` (hasil pemisahan 3NF dari entitas Customer & Kasir).',
                'schema_hint' => 'nota(Nonota, KodeKasir, TglTran, KodeCus)',
                'hint_sql' => "SELECT Nonota, TglTran, KodeCus\nFROM nota\nORDER BY Nonota;",
            ],
            'm2_constraint_fk' => [
                'id' => 'm2_constraint_fk',
                'modul' => 2,
                'title' => 'Uji Referential Integrity (FOREIGN KEY)',
                'description' => 'Buktikan MySQL menolak baris child jika parent key tidak ada.',
                'instruction' => 'Ketik perintah INSERT INTO `itemnota` dengan Nonota \'9999999999\' dan KodeProduk \'P999\' untuk memicu penolakan FOREIGN KEY constraint.',
                'schema_hint' => 'itemnota(Nonota, KodeProduk, banyak, hargajual)',
                'hint_sql' => "INSERT INTO itemnota (Nonota, KodeProduk, banyak, hargajual)\nVALUES ('9999999999', 'P999', 1, 2500);",
            ],
            'm2_constraint_uq' => [
                'id' => 'm2_constraint_uq',
                'modul' => 2,
                'title' => 'Uji UNIQUE Constraint (NamaProduk)',
                'description' => 'Buktikan indeks uq_namaproduk mencegah duplikasi nama barang.',
                'instruction' => 'Ketik perintah INSERT INTO `produk` dengan KodeProduk baru \'P099\' tetapi memakai NamaProduk yang sudah ada: \'Indomie goreng special\'.',
                'schema_hint' => 'produk(KodeProduk, NamaProduk, Harga, stok)',
                'hint_sql' => "INSERT INTO produk (KodeProduk, NamaProduk, Harga, stok)\nVALUES ('P099', 'Indomie goreng special', 5000, 10);",
            ],
            'm3_join_inner_left' => [
                'id' => 'm3_join_inner_left',
                'modul' => 3,
                'title' => 'Praktek JOIN, Table Alias & AS',
                'description' => 'Gabungkan tabel customer dan nota menggunakan alias tabel & alias kolom.',
                'instruction' => 'Ketik query SELECT yang menggabungkan `customer c` dengan `nota n` (INNER JOIN atau LEFT JOIN) pada `c.KodeCus = n.KodeCus`, dan beri alias kolom dengan `AS`.',
                'schema_hint' => 'customer(KodeCus, NamaCus) · nota(Nonota, TglTran, KodeCus)',
                'hint_sql' => "SELECT c.KodeCus AS Kode, c.NamaCus AS Pelanggan, n.Nonota AS Nomor_Nota\nFROM customer c\nLEFT JOIN nota n ON c.KodeCus = n.KodeCus\nORDER BY c.KodeCus;",
            ],
            'm3_view_select' => [
                'id' => 'm3_view_select',
                'modul' => 3,
                'title' => 'Praktek Query VIEW Tersimpan',
                'description' => 'Akses tabel virtual untuk laporan transaksi tanpa menulis ulang JOIN kompleks.',
                'instruction' => 'Ketik query SELECT * dari salah satu VIEW resmi: `viewdetailtransaksi`, `tampiltotalnota`, `daftarhargaproduk`, atau `viewtransaksikasir`.',
                'schema_hint' => 'VIEW: viewdetailtransaksi | tampiltotalnota | daftarhargaproduk | viewtransaksikasir',
                'hint_sql' => "SELECT * FROM viewdetailtransaksi;",
            ],
            'm3_control_flow' => [
                'id' => 'm3_control_flow',
                'modul' => 3,
                'title' => 'Praktek Control Flow (CASE / IF / IFNULL)',
                'description' => 'Terapkan logika percabangan langsung pada hasil query SELECT.',
                'instruction' => 'Ketik query SELECT pada tabel `customer` atau `produk` menggunakan `CASE WHEN ... END`, `IF(...)`, `IFNULL(...)`, atau `NULLIF(...)`.',
                'schema_hint' => 'customer(KodeCus, NamaCus, HPCus, PointTerkumpul) · produk(KodeProduk, NamaProduk, stok)',
                'hint_sql' => "SELECT KodeCus, NamaCus,\n  IFNULL(HPCus, 'Belum Ada') AS Kontak,\n  CASE WHEN PointTerkumpul >= 30 THEN 'Gold' ELSE 'Reguler' END AS Tier\nFROM customer;",
            ],
            'm4_udf_cek_stok' => [
                'id' => 'm4_udf_cek_stok',
                'modul' => 4,
                'title' => 'Praktek Memanggil UDF fcCekStok',
                'description' => 'Latih muscle memory pemanggilan Stored Function lewat SELECT.',
                'instruction' => 'Ketik query SELECT untuk memanggil fungsi `fcCekStok(\'P001\')` dan beri alias `AS cekstok` (ingat: UDF dipanggil dengan SELECT, bukan CALL).',
                'schema_hint' => 'FUNCTION fcCekStok(kd CHAR(4)) RETURNS INT',
                'hint_sql' => "SELECT 'P001' AS KodeProduk, fcCekStok('P001') AS cekstok;",
            ],
            'm4_sp_bonus_point' => [
                'id' => 'm4_sp_bonus_point',
                'modul' => 4,
                'title' => 'Praktek Stored Procedure & CURSOR',
                'description' => 'Eksekusi prosedur ber-cursor dan amati perubahan poin Before vs After.',
                'instruction' => 'Ketik perintah `CALL ProcBonusPoint();` untuk mengeksekusi Stored Procedure ber-CURSOR dan melihat trace iterasinya.',
                'schema_hint' => 'PROCEDURE ProcBonusPoint() · customer(KodeCus, NamaCus, PointTerkumpul)',
                'hint_sql' => "CALL ProcBonusPoint();",
            ],
            'm5_tg_insert_item' => [
                'id' => 'm5_tg_insert_item',
                'modul' => 5,
                'title' => 'Praktek Trigger AFTER INSERT (tg_produk_dijual)',
                'description' => 'Latih DML INSERT yang otomatis memotong stok produk lewat NEW.banyak.',
                'instruction' => 'Ketik `INSERT INTO itemnota (Nonota, KodeProduk, banyak, hargajual) VALUES (\'2611130001\', \'P001\', 2, 2100);` lalu amati stok P001 berkurang otomatis.',
                'schema_hint' => 'itemnota(Nonota, KodeProduk, banyak, hargajual) · Nota tersedia: 2611130001',
                'hint_sql' => "INSERT INTO itemnota (Nonota, KodeProduk, banyak, hargajual)\nVALUES ('2611130001', 'P001', 2, 2100);",
            ],
            'm5_tg_update_kasir' => [
                'id' => 'm5_tg_update_kasir',
                'modul' => 5,
                'title' => 'Praktek Trigger BEFORE UPDATE (tg_update_kasir)',
                'description' => 'Latih DML UPDATE yang mencatat audit OLD dan NEW ke tabel logkasir.',
                'instruction' => 'Ketik perintah `UPDATE kasir SET Pass = \'baru01\' WHERE KodeKasir = \'001\';` untuk memicu pencatatan audit ke `logkasir`.',
                'schema_hint' => 'kasir(KodeKasir, NamaKasir, Pass) · logkasir(IdLog, KodeKasir, Aksi, Keterangan)',
                'hint_sql' => "UPDATE kasir\nSET Pass = 'baru01'\nWHERE KodeKasir = '001';",
            ],
            'm5_tg_delete_item' => [
                'id' => 'm5_tg_delete_item',
                'modul' => 5,
                'title' => 'Praktek Trigger AFTER DELETE (tg_batal_jual)',
                'description' => 'Latih DML DELETE yang mengembalikan stok produk lewat OLD.banyak.',
                'instruction' => 'Ketik perintah `DELETE FROM itemnota WHERE Nonota = \'2208030001\' AND KodeProduk = \'P001\';` untuk melihat stok P001 bertambah kembali.',
                'schema_hint' => 'itemnota(Nonota, KodeProduk, banyak, hargajual)',
                'hint_sql' => "DELETE FROM itemnota\nWHERE Nonota = '2208030001' AND KodeProduk = 'P001';",
            ],
        ];
    }

    public static function scenariosByModul(int $modulNo): array
    {
        return array_values(array_filter(
            self::scenarios(),
            static fn (array $s): bool => $s['modul'] === $modulNo
        ));
    }

    public static function run(string $scenarioId, array $params = []): array
    {
        $catalog = self::scenarios();
        if (!isset($catalog[$scenarioId])) {
            return self::errorResult('', 'Skenario simulasi tidak dikenali atau tidak masuk whitelist.');
        }

        if ($scenarioId === 'm1_norm_stepper') {
            return self::runNormStepper($params);
        }

        $lab = get_db_connection('lab');

        return match ($scenarioId) {
            'm2_constraint_fk' => self::runConstraintFk($lab, $params),
            'm2_constraint_uq' => self::runConstraintUq($lab, $params),
            'm3_join_inner_left' => self::runJoinInnerLeft($lab, $params),
            'm3_view_select' => self::runViewSelect($lab, $params),
            'm3_control_flow' => self::runControlFlow($lab, $params),
            'm4_udf_cek_stok' => self::runUdfCekStok($lab, $params),
            'm4_sp_bonus_point' => self::runSpBonusPoint($lab),
            'm5_tg_insert_item' => self::runTgInsertItem($lab, $params),
            'm5_tg_update_kasir' => self::runTgUpdateKasir($lab, $params),
            'm5_tg_delete_item' => self::runTgDeleteItem($lab, $params),
            default => self::errorResult('', 'Skenario tidak tersedia.'),
        };
    }

    private static function errorResult(string $sql, string $error, array $before = [], array $after = [], string $explanation = ''): array
    {
        return [
            'ok' => false,
            'sql_executed' => $sql,
            'explanation' => $explanation,
            'before' => $before,
            'after' => $after,
            'trace' => null,
            'error' => $error,
        ];
    }

    private static function fetchAllAssoc(PDO $lab, string $sql): array
    {
        $stmt = $lab->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $rows;
    }

    private static function runNormStepper(array $params): array
    {
        $stage = strtolower(trim((string) ($params['stage'] ?? 'unf')));
        $fixtures = require dirname(__DIR__) . '/data/normalisasi_fixtures.php';
        if (!isset($fixtures[$stage])) {
            $stage = 'unf';
        }
        $item = $fixtures[$stage];

        return [
            'ok' => true,
            'sql_executed' => "-- Tahap Normalisasi Aktif: " . strtoupper($stage) . "\n-- Fokus: " . $item['title'],
            'explanation' => $item['description'],
            'before' => [],
            'after' => [],
            'trace' => [
                'kind' => 'normalization',
                'stage' => $stage,
                'label' => $item['label'],
                'tables' => $item['tables'],
            ],
            'error' => null,
        ];
    }

    private static function runConstraintFk(PDO $lab, array $params): array
    {
        $nonota = substr(trim((string) ($params['nonota'] ?? '9999999999')), 0, 10);
        $kodeProduk = substr(trim((string) ($params['kode_produk'] ?? 'P999')), 0, 4);
        if ($nonota === '') {
            $nonota = '9999999999';
        }
        if ($kodeProduk === '') {
            $kodeProduk = 'P999';
        }

        $before = self::fetchAllAssoc($lab, 'SELECT Nonota, KodeProduk, banyak, hargajual FROM itemnota ORDER BY Nonota, KodeProduk');
        $sqlDisplay = "INSERT INTO itemnota (Nonota, KodeProduk, banyak, hargajual)\nVALUES ('{$nonota}', '{$kodeProduk}', 1, 2500);";

        try {
            $stmt = $lab->prepare('INSERT INTO itemnota (Nonota, KodeProduk, banyak, hargajual) VALUES (:no, :kd, 1, 2500)');
            $stmt->execute([':no' => $nonota, ':kd' => $kodeProduk]);
            $after = self::fetchAllAssoc($lab, 'SELECT Nonota, KodeProduk, banyak, hargajual FROM itemnota ORDER BY Nonota, KodeProduk');
            return [
                'ok' => true,
                'sql_executed' => $sqlDisplay,
                'explanation' => 'Insert berhasil karena kedua nilai Foreign Key (Nonota dan KodeProduk) valid di tabel induk nota & produk.',
                'before' => $before,
                'after' => $after,
                'trace' => null,
                'error' => null,
            ];
        } catch (PDOException $e) {
            $after = self::fetchAllAssoc($lab, 'SELECT Nonota, KodeProduk, banyak, hargajual FROM itemnota ORDER BY Nonota, KodeProduk');
            return [
                'ok' => false,
                'sql_executed' => $sqlDisplay,
                'explanation' => 'Referential Integrity aktif! MySQL menolak baris baru di child table itemnota karena Nonota atau KodeProduk tidak ditemukan di parent table.',
                'before' => $before,
                'after' => $after,
                'trace' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    private static function runConstraintUq(PDO $lab, array $params): array
    {
        $namaProduk = substr(trim((string) ($params['nama_produk'] ?? 'Indomie goreng special')), 0, 100);
        if ($namaProduk === '') {
            $namaProduk = 'Indomie goreng special';
        }

        $before = self::fetchAllAssoc($lab, 'SELECT KodeProduk, NamaProduk, Harga, stok FROM produk ORDER BY KodeProduk');
        $sqlDisplay = "INSERT INTO produk (KodeProduk, NamaProduk, Harga, stok)\nVALUES ('P099', '{$namaProduk}', 5000, 10);";

        try {
            $stmt = $lab->prepare("INSERT INTO produk (KodeProduk, NamaProduk, Harga, stok) VALUES ('P099', :nm, 5000, 10)");
            $stmt->execute([':nm' => $namaProduk]);
            $after = self::fetchAllAssoc($lab, 'SELECT KodeProduk, NamaProduk, Harga, stok FROM produk ORDER BY KodeProduk');
            return [
                'ok' => true,
                'sql_executed' => $sqlDisplay,
                'explanation' => 'Insert berhasil karena NamaProduk tersebut belum pernah dipakai di tabel produk.',
                'before' => $before,
                'after' => $after,
                'trace' => null,
                'error' => null,
            ];
        } catch (PDOException $e) {
            $after = self::fetchAllAssoc($lab, 'SELECT KodeProduk, NamaProduk, Harga, stok FROM produk ORDER BY KodeProduk');
            return [
                'ok' => false,
                'sql_executed' => $sqlDisplay,
                'explanation' => 'UNIQUE Constraint (uq_namaproduk) aktif! Walaupun KodeProduk P099 berbeda, NamaProduk yang sama tetap ditolak untuk mencegah duplikasi nama barang.',
                'before' => $before,
                'after' => $after,
                'trace' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    private static function runJoinInnerLeft(PDO $lab, array $params): array
    {
        $joinType = strtolower(trim((string) ($params['join_type'] ?? 'inner')));
        if (!in_array($joinType, ['inner', 'left'], true)) {
            $joinType = 'inner';
        }
        $keyword = $joinType === 'left' ? 'LEFT JOIN' : 'INNER JOIN';

        $sql = "SELECT c.KodeCus AS Kode_Pelanggan, c.NamaCus AS Pelanggan, n.Nonota AS Nomor_Nota, n.TglTran AS Tanggal\n"
             . "FROM customer c\n"
             . "{$keyword} nota n ON c.KodeCus = n.KodeCus\n"
             . "ORDER BY c.KodeCus, n.Nonota;";

        $rows = self::fetchAllAssoc($lab, $sql);
        $explanation = $joinType === 'left'
            ? 'LEFT JOIN mempertahankan seluruh baris Customer (tabel kiri c) termasuk jika belum memiliki transaksi di Nota (kolom Nomor_Nota & Tanggal akan bernilai NULL). Alias c/n menyingkat referensi kolom, dan AS memberi label header.'
            : 'INNER JOIN hanya menampilkan pasangan Customer (c) dan Nota (n) yang cocok pada kunci KodeCus. Alias c dan n mencegah ambiguitas nama kolom.';

        return [
            'ok' => true,
            'sql_executed' => $sql,
            'explanation' => $explanation,
            'before' => [],
            'after' => $rows,
            'trace' => [
                'kind' => 'venn',
                'join_type' => $joinType,
            ],
            'error' => null,
        ];
    }

    private static function runViewSelect(PDO $lab, array $params): array
    {
        $allowedViews = [
            'daftarhargaproduk',
            'tampiltotalnota',
            'viewdetailtransaksi',
            'viewtransaksikasir',
        ];
        $viewName = strtolower(trim((string) ($params['view_name'] ?? 'viewdetailtransaksi')));
        if (!in_array($viewName, $allowedViews, true)) {
            return self::errorResult('', 'Nama VIEW tidak ada dalam whitelist.');
        }

        $sql = "SELECT * FROM `{$viewName}`;";
        $rows = self::fetchAllAssoc($lab, $sql);

        return [
            'ok' => true,
            'sql_executed' => $sql,
            'explanation' => "VIEW `{$viewName}` membaca data terkini secara virtual dari tabel dasarnya tanpa menduplikasi penyimpanan fisik.",
            'before' => [],
            'after' => $rows,
            'trace' => null,
            'error' => null,
        ];
    }

    private static function runControlFlow(PDO $lab, array $params): array
    {
        $func = strtolower(trim((string) ($params['func'] ?? 'case')));
        if (!in_array($func, ['case', 'if', 'ifnull', 'nullif'], true)) {
            $func = 'case';
        }

        $sql = match ($func) {
            'case' => "SELECT KodeCus, NamaCus, PointTerkumpul,\n  CASE\n    WHEN PointTerkumpul >= 30 THEN 'Gold'\n    WHEN PointTerkumpul >= 10 THEN 'Silver'\n    ELSE 'Reguler'\n  END AS Tier_Member\nFROM customer;",
            'if' => "SELECT KodeProduk, NamaProduk, stok,\n  IF(stok >= 50, 'Aman', 'Perlu Restok') AS Status_Stok\nFROM produk;",
            'ifnull' => "SELECT KodeCus, NamaCus,\n  IFNULL(HPCus, 'Belum Diisi') AS Kontak_HP\nFROM customer;",
            'nullif' => "SELECT KodeCus, NamaCus,\n  NULLIF(KodeCus, 'M0000') AS Non_Umum\nFROM customer;",
        };

        $explanation = match ($func) {
            'case' => 'CASE ... WHEN ... ELSE mengevaluasi beberapa kondisi bertingkat untuk mengelompokkan tier pelanggan.',
            'if' => 'IF(kondisi, benar, salah) cocok untuk percabangan biner sederhana langsung di kolom SELECT.',
            'ifnull' => 'IFNULL(ekspresi, nilai_pengganti) mengganti nilai NULL (seperti HPCus milik UMUM) agar tampilan lebih informatif.',
            'nullif' => 'NULLIF(a, b) mengembalikan NULL apabila a = b (berguna untuk menandai akun guest M0000 sebagai NULL).',
        };

        $rows = self::fetchAllAssoc($lab, $sql);
        return [
            'ok' => true,
            'sql_executed' => $sql,
            'explanation' => $explanation,
            'before' => [],
            'after' => $rows,
            'trace' => null,
            'error' => null,
        ];
    }

    private static function runUdfCekStok(PDO $lab, array $params): array
    {
        $allowed = ['P001', 'P002', 'P003'];
        $kd = strtoupper(trim((string) ($params['kode_produk'] ?? 'P001')));
        if (!in_array($kd, $allowed, true)) {
            $kd = 'P001';
        }

        $sqlDisplay = "SELECT '{$kd}' AS KodeProduk, fcCekStok('{$kd}') AS cekstok;";
        $stmt = $lab->prepare("SELECT :kd_col AS KodeProduk, fcCekStok(:kd_fn) AS cekstok");
        $stmt->execute([':kd_col' => $kd, ':kd_fn' => $kd]);
        $udfRow = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        $before = self::fetchAllAssoc($lab, 'SELECT KodeProduk, NamaProduk, stok FROM produk ORDER BY KodeProduk');

        return [
            'ok' => true,
            'sql_executed' => $sqlDisplay,
            'explanation' => "UDF fcCekStok('{$kd}') dipanggil di dalam klausa SELECT dan mengembalikan tepat 1 nilai integer dari kolom stok tabel produk.",
            'before' => $before,
            'after' => $udfRow,
            'trace' => null,
            'error' => null,
        ];
    }

    private static function runSpBonusPoint(PDO $lab): array
    {
        $before = self::fetchAllAssoc($lab, 'SELECT KodeCus, NamaCus, PointTerkumpul FROM customer ORDER BY KodeCus');

        // Emulasi trace loop CURSOR sesuai logika ProcBonusPoint di db_penjualan_lab
        $cursorTrace = [];
        foreach ($before as $idx => $cust) {
            if ($cust['KodeCus'] === 'M0000') {
                continue;
            }
            $pt = (int) $cust['PointTerkumpul'];
            if ($pt >= 100) {
                $bonus = 20;
            } elseif ($pt > 50) {
                $bonus = 15;
            } elseif ($pt > 20) {
                $bonus = 10;
            } else {
                $bonus = 7;
            }
            $cursorTrace[] = [
                'iterasi' => count($cursorTrace) + 1,
                'KodeCus' => $cust['KodeCus'],
                'NamaCus' => $cust['NamaCus'],
                'point_awal' => $pt,
                'bonus' => $bonus,
                'point_akhir' => $pt + $bonus,
            ];
        }

        $stmt = $lab->query('CALL ProcBonusPoint()');
        $spResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return [
            'ok' => true,
            'sql_executed' => 'CALL ProcBonusPoint();',
            'explanation' => 'Stored Procedure ProcBonusPoint() membuka CURSOR pada tabel customer (kecuali M0000), menghitung bonus poin per baris di dalam bonusLoop, memperbarui PointTerkumpul, lalu menutup cursor.',
            'before' => $before,
            'after' => $spResult,
            'trace' => [
                'kind' => 'cursor',
                'rows' => $cursorTrace,
            ],
            'error' => null,
        ];
    }

    private static function runTgInsertItem(PDO $lab, array $params): array
    {
        $allowedProduk = ['P001', 'P002', 'P003'];
        $kd = strtoupper(trim((string) ($params['kode_produk'] ?? 'P001')));
        if (!in_array($kd, $allowedProduk, true)) {
            $kd = 'P001';
        }
        $banyak = max(1, min(10, (int) ($params['banyak'] ?? 2)));
        $nonota = '2611130001';

        $before = self::fetchAllAssoc($lab, 'SELECT KodeProduk, NamaProduk, Harga, stok FROM produk ORDER BY KodeProduk');
        $priceStmt = $lab->prepare('SELECT Harga FROM produk WHERE KodeProduk = :kd LIMIT 1');
        $priceStmt->execute([':kd' => $kd]);
        $harga = (int) $priceStmt->fetchColumn();
        $priceStmt->closeCursor();

        $sqlDisplay = "INSERT INTO itemnota (Nonota, KodeProduk, banyak, hargajual)\n"
                    . "VALUES ('{$nonota}', '{$kd}', {$banyak}, {$harga});";

        try {
            $stmt = $lab->prepare(
                'INSERT INTO itemnota (Nonota, KodeProduk, banyak, hargajual)
                 VALUES (:no, :kd, :qty, :harga)'
            );
            $stmt->execute([':no' => $nonota, ':kd' => $kd, ':qty' => $banyak, ':harga' => $harga]);
            $after = self::fetchAllAssoc($lab, 'SELECT KodeProduk, NamaProduk, Harga, stok FROM produk ORDER BY KodeProduk');

            return [
                'ok' => true,
                'sql_executed' => $sqlDisplay,
                'explanation' => "Trigger AFTER INSERT `tg_produk_dijual` otomatis dieksekusi oleh MySQL dan mengurangi stok {$kd} sebesar NEW.banyak ({$banyak}).",
                'before' => $before,
                'after' => $after,
                'trace' => null,
                'error' => null,
            ];
        } catch (PDOException $e) {
            $after = self::fetchAllAssoc($lab, 'SELECT KodeProduk, NamaProduk, Harga, stok FROM produk ORDER BY KodeProduk');
            return self::errorResult(
                $sqlDisplay,
                $e->getMessage(),
                $before,
                $after,
                "Item ({$nonota}, {$kd}) sudah ada di itemnota atau melanggar constraint. Jalankan skenario Hapus Item atau klik 'Reset Snapshot Lab' untuk mengembalikan kondisi awal."
            );
        }
    }

    private static function runTgUpdateKasir(PDO $lab, array $params): array
    {
        $allowedKasir = ['001', '002', '003', '005'];
        $kd = trim((string) ($params['kode_kasir'] ?? '001'));
        if (!in_array($kd, $allowedKasir, true)) {
            $kd = '001';
        }
        $passBaru = substr(preg_replace('/[^a-zA-Z0-9_]/', '', (string) ($params['pass_baru'] ?? 'baru01')), 0, 6);
        if ($passBaru === '') {
            $passBaru = 'baru01';
        }

        $before = self::fetchAllAssoc($lab, 'SELECT IdLog, KodeKasir, Aksi, Waktu, Keterangan FROM logkasir ORDER BY IdLog DESC LIMIT 10');
        $sqlDisplay = "UPDATE kasir SET Pass = '{$passBaru}' WHERE KodeKasir = '{$kd}';";

        $stmt = $lab->prepare('UPDATE kasir SET Pass = :pass WHERE KodeKasir = :kd');
        $stmt->execute([':pass' => $passBaru, ':kd' => $kd]);

        $after = self::fetchAllAssoc($lab, 'SELECT IdLog, KodeKasir, Aksi, Waktu, Keterangan FROM logkasir ORDER BY IdLog DESC LIMIT 10');

        return [
            'ok' => true,
            'sql_executed' => $sqlDisplay,
            'explanation' => "Trigger BEFORE UPDATE `tg_update_kasir` otomatis mencatat perubahan password lama (OLD.Pass) ke password baru (NEW.Pass) pada tabel logkasir.",
            'before' => $before,
            'after' => $after,
            'trace' => null,
            'error' => null,
        ];
    }

    private static function runTgDeleteItem(PDO $lab, array $params): array
    {
        $allowedNota = ['2208030001', '2208030002', '2208040001', '2208050001', '2611130001'];
        $allowedProduk = ['P001', 'P002', 'P003'];
        $nonota = trim((string) ($params['nonota'] ?? '2208030001'));
        $kd = strtoupper(trim((string) ($params['kode_produk'] ?? 'P001')));
        if (!in_array($nonota, $allowedNota, true)) {
            $nonota = '2208030001';
        }
        if (!in_array($kd, $allowedProduk, true)) {
            $kd = 'P001';
        }

        $before = self::fetchAllAssoc($lab, 'SELECT KodeProduk, NamaProduk, Harga, stok FROM produk ORDER BY KodeProduk');
        $sqlDisplay = "DELETE FROM itemnota WHERE Nonota = '{$nonota}' AND KodeProduk = '{$kd}';";

        $stmt = $lab->prepare('DELETE FROM itemnota WHERE Nonota = :no AND KodeProduk = :kd');
        $stmt->execute([':no' => $nonota, ':kd' => $kd]);
        $deletedCount = $stmt->rowCount();

        $after = self::fetchAllAssoc($lab, 'SELECT KodeProduk, NamaProduk, Harga, stok FROM produk ORDER BY KodeProduk');

        if ($deletedCount === 0) {
            return [
                'ok' => false,
                'sql_executed' => $sqlDisplay,
                'explanation' => "Tidak ada baris ({$nonota}, {$kd}) di itemnota sehingga trigger tg_batal_jual tidak terpanggil.",
                'before' => $before,
                'after' => $after,
                'trace' => null,
                'error' => "Baris itemnota ({$nonota}, {$kd}) tidak ditemukan.",
            ];
        }

        return [
            'ok' => true,
            'sql_executed' => $sqlDisplay,
            'explanation' => "Trigger AFTER DELETE `tg_batal_jual` otomatis mengembalikan stok {$kd} sebesar OLD.banyak dari baris itemnota yang dihapus.",
            'before' => $before,
            'after' => $after,
            'trace' => null,
            'error' => null,
        ];
    }

    /**
     * Menjalankan query SQL praktek yang diketik langsung oleh user di Sandbox db_penjualan_lab.
     */
    public static function executePracticeQuery(string $scenarioId, string $userSql): array
    {
        $catalog = self::scenarios();
        if (!isset($catalog[$scenarioId])) {
            return [
                'ok' => false,
                'matched_instruction' => false,
                'feedback' => 'Skenario tidak valid.',
                'sql_executed' => '',
                'explanation' => '',
                'before' => [],
                'after' => [],
                'trace' => null,
                'error' => 'Skenario simulasi tidak dikenali.',
            ];
        }

        $cleanSql = self::sanitizeLabQuery($userSql);
        if ($cleanSql['ok'] === false) {
            return [
                'ok' => false,
                'matched_instruction' => false,
                'feedback' => 'Query ditolak oleh penjaga keamanan sandbox.',
                'sql_executed' => trim($userSql),
                'explanation' => 'Sandbox hanya mengizinkan satu statement SQL (SELECT, INSERT, UPDATE, DELETE, CALL, SHOW, DESCRIBE) pada db_penjualan_lab.',
                'before' => [],
                'after' => [],
                'trace' => null,
                'error' => $cleanSql['error'],
            ];
        }

        $sql = $cleanSql['sql'];
        $lab = get_db_connection('lab');

        $before = [];
        if (in_array($scenarioId, ['m4_sp_bonus_point'], true)) {
            $before = self::fetchAllAssoc($lab, 'SELECT KodeCus, NamaCus, PointTerkumpul FROM customer ORDER BY KodeCus');
        } elseif (in_array($scenarioId, ['m5_tg_insert_item', 'm5_tg_delete_item'], true)) {
            $before = self::fetchAllAssoc($lab, 'SELECT KodeProduk, NamaProduk, Harga, stok FROM produk ORDER BY KodeProduk');
        } elseif ($scenarioId === 'm5_tg_update_kasir') {
            $before = self::fetchAllAssoc($lab, 'SELECT IdLog, KodeKasir, Aksi, Waktu, Keterangan FROM logkasir ORDER BY IdLog DESC LIMIT 10');
        }

        $cursorTrace = null;
        if ($scenarioId === 'm4_sp_bonus_point' && preg_match('/^CALL\s+ProcBonusPoint\s*\(\s*\)/i', $sql)) {
            $cursorTrace = [];
            foreach ($before as $cust) {
                if ($cust['KodeCus'] === 'M0000') {
                    continue;
                }
                $pt = (int) $cust['PointTerkumpul'];
                $bonus = $pt >= 100 ? 20 : ($pt > 50 ? 15 : ($pt > 20 ? 10 : 7));
                $cursorTrace[] = [
                    'iterasi' => count($cursorTrace) + 1,
                    'KodeCus' => $cust['KodeCus'],
                    'NamaCus' => $cust['NamaCus'],
                    'point_awal' => $pt,
                    'bonus' => $bonus,
                    'point_akhir' => $pt + $bonus,
                ];
            }
        }

        try {
            $stmt = $lab->query($sql);
            $rows = [];
            if ($stmt !== false && $stmt->columnCount() > 0) {
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            if ($stmt !== false) {
                $stmt->closeCursor();
            }

            $after = $rows;
            if (in_array($scenarioId, ['m5_tg_insert_item', 'm5_tg_delete_item'], true)) {
                $after = self::fetchAllAssoc($lab, 'SELECT KodeProduk, NamaProduk, Harga, stok FROM produk ORDER BY KodeProduk');
            } elseif ($scenarioId === 'm5_tg_update_kasir') {
                $after = self::fetchAllAssoc($lab, 'SELECT IdLog, KodeKasir, Aksi, Waktu, Keterangan FROM logkasir ORDER BY IdLog DESC LIMIT 10');
            }

            $match = self::evaluatePracticeMatch($scenarioId, $sql, true, null);
            $trace = null;
            if ($cursorTrace !== null) {
                $trace = ['kind' => 'cursor', 'rows' => $cursorTrace];
            } elseif ($scenarioId === 'm3_join_inner_left' && $match['matched']) {
                $trace = [
                    'kind' => 'venn',
                    'join_type' => stripos($sql, 'LEFT JOIN') !== false ? 'left' : 'inner',
                ];
            }

            return [
                'ok' => true,
                'matched_instruction' => $match['matched'],
                'feedback' => $match['feedback'],
                'sql_executed' => $sql . ';',
                'explanation' => $match['explanation'],
                'before' => $before,
                'after' => $after,
                'trace' => $trace,
                'error' => null,
            ];
        } catch (PDOException $e) {
            $after = $before;
            $match = self::evaluatePracticeMatch($scenarioId, $sql, false, $e->getMessage());

            return [
                'ok' => $match['matched'],
                'matched_instruction' => $match['matched'],
                'feedback' => $match['feedback'],
                'sql_executed' => $sql . ';',
                'explanation' => $match['explanation'],
                'before' => $before,
                'after' => $after,
                'trace' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    private static function sanitizeLabQuery(string $raw): array
    {
        $trimmed = trim($raw);
        // Hapus satu titik koma di paling akhir jika ada
        $trimmed = rtrim($trimmed, "; \t\n\r\0\x0B");
        if ($trimmed === '') {
            return ['ok' => false, 'sql' => '', 'error' => 'Editor SQL masih kosong. Ketik query sesuai instruksi terlebih dahulu.'];
        }
        if (str_contains($trimmed, ';')) {
            return ['ok' => false, 'sql' => '', 'error' => 'Hanya satu perintah SQL per eksekusi yang diizinkan (tanpa multi-statement `;`).'];
        }
        if (!preg_match('/^(SELECT|INSERT|UPDATE|DELETE|CALL|SHOW|DESCRIBE|DESC|EXPLAIN)\b/i', $trimmed)) {
            return ['ok' => false, 'sql' => '', 'error' => 'Hanya perintah SELECT, INSERT, UPDATE, DELETE, CALL, SHOW, atau DESCRIBE yang diizinkan di sandbox.'];
        }
        $blocked = '/(?:@@|\b(?:DROP|TRUNCATE|ALTER|CREATE|GRANT|REVOKE|RENAME|REPLACE|LOAD|LOAD_FILE|OUTFILE|DUMPFILE|INFORMATION_SCHEMA|PERFORMANCE_SCHEMA|SYS|MYSQL|DB_SMBD_KUIS|SLEEP|BENCHMARK|CURRENT_USER|SESSION_USER|SYSTEM_USER)\b|\b(?:USER|VERSION)\s*\()/i';
        if (preg_match($blocked, $trimmed, $m)) {
            return ['ok' => false, 'sql' => '', 'error' => 'Ekspresi `' . strtoupper($m[0]) . '` diblokir demi keamanan database lab.'];
        }

        return ['ok' => true, 'sql' => $trimmed, 'error' => null];
    }

    private static function evaluatePracticeMatch(string $scenarioId, string $sql, bool $executedOk, ?string $err): array
    {
        $u = strtoupper($sql);
        $errLower = strtolower((string) $err);

        return match ($scenarioId) {
            'm1_norm_stepper' => ($executedOk && preg_match('/\bSELECT\b.*\bFROM\s+`?nota`?\b/is', $sql))
                ? [
                    'matched' => true,
                    'feedback' => 'Tepat! Query 3NF tabel nota berhasil dieksekusi.',
                    'explanation' => 'Tabel nota pada 3NF hanya menyimpan KodeKasir & KodeCus sebagai Foreign Key sehingga bebas redundansi.',
                ]
                : [
                    'matched' => false,
                    'feedback' => 'Query berjalan, namun pastikan memilih kolom dari tabel `nota`.',
                    'explanation' => 'Gunakan SELECT Nonota, TglTran, KodeCus FROM nota; sesuai instruksi.',
                ],
            'm2_constraint_fk' => (!$executedOk && (str_contains($errLower, 'foreign key') || str_contains($errLower, '1452')))
                ? [
                    'matched' => true,
                    'feedback' => 'Tepat! FOREIGN KEY constraint berhasil terpicu dan menolak baris yatim.',
                    'explanation' => 'Referential Integrity menjaga agar itemnota tidak bisa mereferensikan Nonota atau KodeProduk yang tidak ada.',
                ]
                : [
                    'matched' => false,
                    'feedback' => $executedOk
                        ? 'INSERT berhasil masuk karena key valid — coba pakai Nonota/KodeProduk yang tidak ada untuk menguji error FK.'
                        : 'Terjadi error SQL lain, periksa sintaks INSERT INTO itemnota.',
                    'explanation' => 'Untuk membuktikan FK, masukkan nilai yang tidak ada di tabel induk (mis. Nonota 9999999999, KodeProduk P999).',
                ],
            'm2_constraint_uq' => (!$executedOk && (str_contains($errLower, 'duplicate entry') || str_contains($errLower, '1062')))
                ? [
                    'matched' => true,
                    'feedback' => 'Tepat! UNIQUE constraint `uq_namaproduk` berhasil menolak duplikasi NamaProduk.',
                    'explanation' => 'Meskipun KodeProduk berbeda, NamaProduk yang sama tetap ditolak oleh indeks UNIQUE.',
                ]
                : [
                    'matched' => false,
                    'feedback' => $executedOk
                        ? 'INSERT berhasil karena NamaProduk unik — gunakan NamaProduk yang sudah ada (mis. Indomie goreng special) untuk menguji UNIQUE.'
                        : 'Terjadi error SQL lain, periksa sintaks INSERT INTO produk.',
                    'explanation' => 'Gunakan KodeProduk baru (P099) dengan NamaProduk yang sama persis dengan data seed.',
                ],
            'm3_join_inner_left' => ($executedOk && str_contains($u, 'JOIN') && str_contains($u, 'CUSTOMER') && str_contains($u, 'NOTA') && str_contains($u, ' AS '))
                ? [
                    'matched' => true,
                    'feedback' => 'Tepat! Query JOIN dengan Table Alias & AS berhasil dieksekusi.',
                    'explanation' => 'Table alias (c, n) mencegah ambiguitas kolom dan AS memperjelas judul kolom hasil.',
                ]
                : [
                    'matched' => false,
                    'feedback' => 'Query berjalan, namun pastikan menggabungkan `customer` dan `nota` dengan `JOIN` serta memakai alias `AS`.',
                    'explanation' => 'Coba sertakan INNER JOIN atau LEFT JOIN antara customer c dan nota n serta alias kolom AS.',
                ],
            'm3_view_select' => ($executedOk && preg_match('/\b(daftarhargaproduk|tampiltotalnota|viewdetailtransaksi|viewtransaksikasir)\b/i', $sql))
                ? [
                    'matched' => true,
                    'feedback' => 'Tepat! VIEW berhasil dipanggil.',
                    'explanation' => 'VIEW bertindak sebagai tabel virtual yang selalu membaca data terbaru dari tabel dasarnya.',
                ]
                : [
                    'matched' => false,
                    'feedback' => 'Query berjalan, namun pastikan memanggil salah satu dari 4 VIEW resmi.',
                    'explanation' => 'Pilih dari: viewdetailtransaksi, tampiltotalnota, daftarhargaproduk, atau viewtransaksikasir.',
                ],
            'm3_control_flow' => ($executedOk && preg_match('/\b(CASE|IF|IFNULL|NULLIF)\b/i', $sql))
                ? [
                    'matched' => true,
                    'feedback' => 'Tepat! Ekspresi Control Flow berhasil dieksekusi.',
                    'explanation' => 'Fungsi CASE/IF/IFNULL/NULLIF memformat dan mengelompokkan nilai langsung di sisi DBMS.',
                ]
                : [
                    'matched' => false,
                    'feedback' => 'Query berjalan, namun belum menggunakan fungsi Control Flow (CASE/IF/IFNULL/NULLIF).',
                    'explanation' => 'Tambahkan salah satu fungsi control flow pada klausa SELECT.',
                ],
            'm4_udf_cek_stok' => ($executedOk && preg_match('/\bSELECT\b.*\bfcCekStok\s*\(/is', $sql))
                ? [
                    'matched' => true,
                    'feedback' => 'Tepat! UDF fcCekStok berhasil dipanggil lewat SELECT.',
                    'explanation' => 'Berbeda dari Stored Procedure (CALL), User-Defined Function mengembalikan nilai via RETURN dan dipanggil di SELECT.',
                ]
                : [
                    'matched' => false,
                    'feedback' => 'Pastikan memanggil `fcCekStok(...)` di dalam perintah `SELECT`.',
                    'explanation' => 'Contoh: SELECT fcCekStok(\'P001\') AS cekstok;',
                ],
            'm4_sp_bonus_point' => ($executedOk && preg_match('/^CALL\s+ProcBonusPoint\s*\(\s*\)/i', $sql))
                ? [
                    'matched' => true,
                    'feedback' => 'Tepat! Stored Procedure ProcBonusPoint() & CURSOR berhasil dijalankan.',
                    'explanation' => 'Perhatikan perubahan PointTerkumpul Before vs After dan langkah iterasi CURSOR di bawah.',
                ]
                : [
                    'matched' => false,
                    'feedback' => 'Pastikan memanggil `CALL ProcBonusPoint();`.',
                    'explanation' => 'Stored Procedure dipanggil menggunakan kata kunci CALL.',
                ],
            'm5_tg_insert_item' => ($executedOk && preg_match('/^INSERT\s+INTO\s+`?itemnota`?\b/i', $sql))
                ? [
                    'matched' => true,
                    'feedback' => 'Tepat! Trigger AFTER INSERT `tg_produk_dijual` otomatis memotong stok produk.',
                    'explanation' => 'Bandingkan kolom stok pada tabel Before vs After di bawah.',
                ]
                : [
                    'matched' => false,
                    'feedback' => $executedOk ? 'Gunakan perintah INSERT INTO itemnota.' : 'Error eksekusi — jika Duplicate entry, klik Reset Snapshot Lab terlebih dahulu.',
                    'explanation' => 'Trigger tg_produk_dijual hanya terpicu saat terjadi INSERT pada tabel itemnota.',
                ],
            'm5_tg_update_kasir' => ($executedOk && preg_match('/^UPDATE\s+`?kasir`?\b/i', $sql))
                ? [
                    'matched' => true,
                    'feedback' => 'Tepat! Trigger BEFORE UPDATE `tg_update_kasir` mencatat audit ke `logkasir`.',
                    'explanation' => 'Bandingkan isi logkasir Before vs After untuk melihat rekaman OLD & NEW.',
                ]
                : [
                    'matched' => false,
                    'feedback' => 'Gunakan perintah UPDATE kasir SET Pass = ... WHERE KodeKasir = ...',
                    'explanation' => 'Trigger tg_update_kasir hanya terpicu saat baris pada tabel kasir diperbarui.',
                ],
            'm5_tg_delete_item' => ($executedOk && preg_match('/^DELETE\s+FROM\s+`?itemnota`?\b/i', $sql))
                ? [
                    'matched' => true,
                    'feedback' => 'Tepat! Trigger AFTER DELETE `tg_batal_jual` mengembalikan stok produk.',
                    'explanation' => 'Bandingkan kolom stok Before vs After untuk melihat efek OLD.banyak.',
                ]
                : [
                    'matched' => false,
                    'feedback' => 'Gunakan perintah DELETE FROM itemnota WHERE ...',
                    'explanation' => 'Trigger tg_batal_jual hanya terpicu saat baris pada itemnota dihapus.',
                ],
            default => [
                'matched' => $executedOk,
                'feedback' => $executedOk ? 'Query berhasil dieksekusi.' : 'Query gagal dieksekusi.',
                'explanation' => '',
            ],
        };
    }
}
