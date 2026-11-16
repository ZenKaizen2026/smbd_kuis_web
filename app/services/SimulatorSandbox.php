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
                'title' => 'Stepper Normalisasi (UNF → 1NF → 2NF → 3NF)',
                'description' => 'Telusuri evolusi tabel transaksi Nota menjadi skema 3NF db_penjualan tanpa repeating group, partial dependency, maupun transitive dependency.',
            ],
            'm2_constraint_fk' => [
                'id' => 'm2_constraint_fk',
                'modul' => 2,
                'title' => 'Uji Referential Integrity (FOREIGN KEY)',
                'description' => 'Coba masukkan baris ke ItemNota dengan NoNota atau KodeProduk yang tidak terdaftar untuk melihat penolakan FOREIGN KEY MySQL.',
            ],
            'm2_constraint_uq' => [
                'id' => 'm2_constraint_uq',
                'modul' => 2,
                'title' => 'Uji UNIQUE Constraint (NamaProduk)',
                'description' => 'Coba tambahkan produk baru dengan NamaProduk yang sudah ada untuk melihat error Duplicate entry pada indeks uq_namaproduk.',
            ],
            'm3_join_inner_left' => [
                'id' => 'm3_join_inner_left',
                'modul' => 3,
                'title' => 'Perbandingan INNER JOIN vs LEFT JOIN',
                'description' => 'Bandingkan hasil relasi Customer c JOIN Nota n lengkap dengan penjelasan Table Alias (c, n) dan diagram Venn.',
            ],
            'm3_view_select' => [
                'id' => 'm3_view_select',
                'modul' => 3,
                'title' => 'Eksekusi VIEW Tersimpan',
                'description' => 'Jalankan SELECT * pada 4 VIEW resmi db_penjualan_lab (daftarhargaproduk, tampiltotalnota, viewdetailtransaksi, viewtransaksikasir).',
            ],
            'm3_control_flow' => [
                'id' => 'm3_control_flow',
                'modul' => 3,
                'title' => 'Fungsi Control Flow (CASE, IF, IFNULL, NULLIF)',
                'description' => 'Uji percabangan nilai langsung di dalam query SELECT terhadap tabel customer dan produk.',
            ],
            'm4_udf_cek_stok' => [
                'id' => 'm4_udf_cek_stok',
                'modul' => 4,
                'title' => 'Panggil UDF fcCekStok(kd)',
                'description' => 'Jalankan User-Defined Function SELECT fcCekStok(...) dan bandingkan dengan data stok aktual di tabel produk.',
            ],
            'm4_sp_bonus_point' => [
                'id' => 'm4_sp_bonus_point',
                'modul' => 4,
                'title' => 'Stored Procedure & Trace CURSOR (ProcBonusPoint)',
                'description' => 'Eksekusi CALL ProcBonusPoint() dan amati perubahan PointTerkumpul Before vs After beserta trace iterasi CURSOR.',
            ],
            'm5_tg_insert_item' => [
                'id' => 'm5_tg_insert_item',
                'modul' => 5,
                'title' => 'Trigger AFTER INSERT (tg_produk_dijual)',
                'description' => 'Tambahkan item pada Nota 2611130001 lalu amati stok produk berkurang otomatis oleh pseudorow NEW.banyak.',
            ],
            'm5_tg_update_kasir' => [
                'id' => 'm5_tg_update_kasir',
                'modul' => 5,
                'title' => 'Trigger BEFORE UPDATE (tg_update_kasir)',
                'description' => 'Ubah password kasir untuk melihat bagaimana OLD dan NEW dicatat otomatis ke tabel audit logkasir.',
            ],
            'm5_tg_delete_item' => [
                'id' => 'm5_tg_delete_item',
                'modul' => 5,
                'title' => 'Trigger AFTER DELETE (tg_batal_jual)',
                'description' => 'Hapus baris dari itemnota lalu amati stok produk dikembalikan otomatis menggunakan pseudorow OLD.banyak.',
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
}
