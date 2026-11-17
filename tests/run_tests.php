<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/config/database.php';
require_once dirname(__DIR__) . '/app/helpers/csrf.php';
require_once dirname(__DIR__) . '/app/helpers/auth.php';
require_once dirname(__DIR__) . '/app/helpers/code_ui.php';
require_once dirname(__DIR__) . '/app/services/SnapshotResetter.php';
require_once dirname(__DIR__) . '/app/services/ProgressService.php';
require_once dirname(__DIR__) . '/app/services/CustomModuleService.php';
require_once dirname(__DIR__) . '/app/services/ExtraModuleService.php';
require_once dirname(__DIR__) . '/app/services/QuizEngine.php';
require_once dirname(__DIR__) . '/app/services/SimulatorSandbox.php';

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

$passed = 0;
$failed = 0;

function test_case(string $name, callable $fn): void
{
    global $passed, $failed;
    try {
        $fn();
        echo "[PASS] {$name}\n";
        $passed++;
    } catch (Throwable $e) {
        echo "[FAIL] {$name}: {$e->getMessage()}\n";
        $failed++;
    }
}

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

test_case('PDO connection to db_smbd_kuis & db_penjualan_lab', static function (): void {
    $app = get_db_connection('app');
    $lab = get_db_connection('lab');
    assert_true((int) $app->query('SELECT 1')->fetchColumn() === 1, 'db_smbd_kuis ping failed');
    assert_true((int) $lab->query('SELECT 1')->fetchColumn() === 1, 'db_penjualan_lab ping failed');
});

test_case('Seeder account mahasiswa_demo exists and password verifies', static function (): void {
    $app = get_db_connection('app');
    $stmt = $app->prepare('SELECT id, password_hash FROM app_users WHERE nim_or_username = :u LIMIT 1');
    $stmt->execute([':u' => 'mahasiswa_demo']);
    $row = $stmt->fetch();
    assert_true(is_array($row), 'Seeder user mahasiswa_demo not found');
    assert_true(password_verify('demo1234', $row['password_hash']), 'Seeder password verification failed');
});

test_case('CSRF token generation & verification', static function (): void {
    $_SESSION = [];
    $tok = csrf_token();
    assert_true(strlen($tok) === 64, 'CSRF token must be 64 hex chars');
    assert_true(csrf_verify($tok), 'Valid CSRF token rejected');
    assert_true(!csrf_verify('invalid_token'), 'Invalid CSRF token accepted');
    assert_true(!csrf_verify(null), 'Null CSRF token accepted');
});

test_case('Register, duplicate check, login success & failure (isolated cleanup)', static function (): void {
    $app = get_db_connection('app');
    $testUser = 'test_mhs_' . bin2hex(random_bytes(4));

    try {
        $reg = register_user($app, $testUser, 'Tester Unit', 'secret123');
        assert_true($reg['ok'] === true, 'Register failed: ' . ($reg['error'] ?? ''));

        $dup = register_user($app, $testUser, 'Tester Unit', 'secret123');
        assert_true($dup['ok'] === false, 'Duplicate username must be rejected');

        $badLogin = attempt_login($app, $testUser, 'wrongpass');
        assert_true($badLogin['ok'] === false, 'Wrong password must fail');

        $_SESSION = [];
        $goodLogin = attempt_login($app, $testUser, 'secret123');
        assert_true($goodLogin['ok'] === true, 'Valid login failed');
        assert_true(($_SESSION['username'] ?? '') === $testUser, 'Session username mismatch');
    } finally {
        $del = $app->prepare('DELETE FROM app_users WHERE nim_or_username = :u');
        $del->execute([':u' => $testUser]);
    }
});

test_case('SnapshotResetter preserves exact seed stock and clean LogKasir', static function (): void {
    SnapshotResetter::resetLabDatabase();
    $lab = get_db_connection('lab');
    $stokP001 = (int) $lab->query("SELECT fcCekStok('P001')")->fetchColumn();
    $logCount = (int) $lab->query('SELECT COUNT(*) FROM logkasir')->fetchColumn();
    assert_true($stokP001 === 97, "Expected P001 stok=97 after reset, got {$stokP001}");
    assert_true($logCount === 0, "Expected logkasir count=0 after reset, got {$logCount}");
});

test_case('ProgressService & CustomModuleService CRUD + IDOR check', static function (): void {
    $app = get_db_connection('app');
    $stmt = $app->prepare('SELECT id FROM app_users WHERE nim_or_username = :u LIMIT 1');
    $stmt->execute([':u' => 'mahasiswa_demo']);
    $demoId = (int) $stmt->fetchColumn();
    assert_true($demoId > 0, 'Demo user ID not found');

    $summary = ProgressService::getDashboardSummary($app, $demoId);
    assert_true(count($summary['modules']) === 5, 'Dashboard must return 5 modules');

    $created = CustomModuleService::create($app, $demoId, 3, 'Catatan Alias', 't1/n adalah alias tabel', 'SELECT n.Nonota FROM Nota n;');
    assert_true($created['ok'] === true, 'CustomModule create failed');
    $noteId = (int) $created['id'];

    try {
        $list = CustomModuleService::listByUser($app, $demoId, 3);
        assert_true(count($list) >= 1, 'Expected at least 1 custom note for Modul 3');

        // IDOR check: user ID lain (999999) tidak boleh bisa menghapus catatan user ini
        $idorDelete = CustomModuleService::delete($app, 999999, $noteId);
        assert_true($idorDelete === false, 'IDOR: foreign user must not delete note');

        $ownerDelete = CustomModuleService::delete($app, $demoId, $noteId);
        assert_true($ownerDelete === true, 'Owner delete failed');
    } finally {
        CustomModuleService::delete($app, $demoId, $noteId);
    }
});

test_case('is_admin only grants admin to mahasiswa_demo', function (): void {
    assert_true(is_admin(['nim_or_username' => 'mahasiswa_demo']), 'mahasiswa_demo harus dikenali sebagai admin');
    assert_true(!is_admin(['nim_or_username' => 'mahasiswa_lain']), 'user biasa bukan admin');
    assert_true(!is_admin(null), 'null bukan admin');
});

test_case('ExtraModuleService::parseMarkdown extracts metadata and escapes XSS', function (): void {
    $md = "# Judul Ekstra\n\n## Topik A\nPenjelasan ringkas materi.\n\n## Topik B\n<script>alert('xss')</script>";
    $parsed = ExtraModuleService::parseMarkdown($md);

    assert_true($parsed['title'] === 'Judul Ekstra', 'Judul harus diambil dari heading H1');
    assert_true(in_array('Topik A', $parsed['topics'], true), 'Topik A harus terdeteksi');
    assert_true(in_array('Topik B', $parsed['topics'], true), 'Topik B harus terdeteksi');
    assert_true(str_contains($parsed['summary'], 'Penjelasan ringkas materi.'), 'Summary harus berisi paragraf');
    assert_true(!str_contains($parsed['html'], '<script>'), 'Tag script mentah tidak boleh lolos ke HTML');
    assert_true(str_contains($parsed['html'], '&lt;script&gt;'), 'Tag script harus di-escape');
});

test_case('ExtraModuleService CRUD & file cleanup with temporary .md', function (): void {
    $app = get_db_connection('app');
    $stmt = $app->prepare("SELECT id FROM app_users WHERE nim_or_username = 'mahasiswa_demo' LIMIT 1");
    $stmt->execute();
    $demoId = (int) $stmt->fetchColumn();
    assert_true($demoId > 0, 'Demo admin user harus tersedia');

    $tmpFile = tempnam(sys_get_temp_dir(), 'smbd_mod_');
    assert_true($tmpFile !== false, 'Gagal membuat file sementara');
    file_put_contents($tmpFile, "# Modul Uji Ekstra\n\n## Subtopik Satu\nKonten pengujian upload markdown.\n");

    $createdId = 0;
    try {
        $created = ExtraModuleService::processAndCreate(
            $app,
            $demoId,
            [
                'name' => 'uji_ekstra.md',
                'tmp_name' => $tmpFile,
                'size' => filesize($tmpFile),
                'error' => UPLOAD_ERR_OK,
            ],
            'MOD-TEST',
            'Judul Override Uji'
        );

        assert_true($created['ok'] === true, 'processAndCreate harus mengembalikan ok=true');
        $createdId = (int) $created['id'];
        assert_true($createdId > 0, 'ID modul ekstra harus > 0');

        $found = ExtraModuleService::findById($app, $createdId);
        assert_true($found !== null, 'findById harus menemukan modul ekstra');
        assert_true($found['judul'] === 'Judul Override Uji', 'Custom title harus dipakai');
        assert_true($found['source_type'] === 'md', 'source_type harus md');

        $all = ExtraModuleService::listAll($app);
        $ids = array_map(static fn (array $r): int => (int) $r['id'], $all);
        assert_true(in_array($createdId, $ids, true), 'listAll harus memuat modul yang baru dibuat');
    } finally {
        if ($createdId > 0) {
            $deleted = ExtraModuleService::delete($app, $createdId);
            assert_true($deleted, 'delete harus berhasil menghapus record & file');
        }
        if (is_file($tmpFile)) {
            @unlink($tmpFile);
        }
    }
});

test_case('Quiz bank Modul 1-5 contract validation', function (): void {
    $allowedTypes = ['mcq', 'output_trace', 'syntax_debug'];
    for ($m = 1; $m <= 5; $m++) {
        $qs = QuizEngine::loadQuestions($m);
        assert_true(count($qs) === 5, "Modul {$m} harus memiliki tepat 5 soal");
        $ids = [];
        foreach ($qs as $q) {
            assert_true(!empty($q['id']) && !in_array($q['id'], $ids, true), "ID soal Modul {$m} harus unik");
            $ids[] = $q['id'];
            assert_true(in_array($q['type'], $allowedTypes, true), "Tipe soal {$q['id']} tidak valid");
            assert_true(count($q['options']) === 4, "Opsi soal {$q['id']} harus 4");
            assert_true(array_key_exists($q['correct_key'], $q['options']), "correct_key {$q['id']} harus ada di options");
            assert_true(trim((string) $q['explanation']) !== '', "Pembahasan {$q['id']} wajib diisi");
        }
    }
});

test_case('QuizEngine evaluateAndSave, threshold 75%, progress upsert & IDOR check', function (): void {
    $app = get_db_connection('app');
    $testUser = 'quiz_test_' . bin2hex(random_bytes(4));
    $ins = $app->prepare('INSERT INTO app_users (nim_or_username, nama_lengkap, password_hash) VALUES (:u, :n, :p)');
    $ins->execute([
        ':u' => $testUser,
        ':n' => 'Quiz Tester',
        ':p' => password_hash('secret123', PASSWORD_DEFAULT),
    ]);
    $uid = (int) $app->lastInsertId();

    try {
        $qs = QuizEngine::loadQuestions(1);
        $passAnswers = [];
        foreach ($qs as $idx => $q) {
            // 4 benar, 1 salah -> 80% (>= 75% => paham)
            $passAnswers[$q['id']] = ($idx < 4) ? $q['correct_key'] : 'D';
            if ($idx === 4 && $q['correct_key'] === 'D') {
                $passAnswers[$q['id']] = 'A';
            }
        }
        $resPass = QuizEngine::evaluateAndSave($app, $uid, 1, $passAnswers, 42);
        assert_true($resPass['score_percent'] === 80.0, 'Skor 4/5 harus 80%');
        assert_true($resPass['passed'] === true, '80% harus lulus');
        assert_true($resPass['status'] === 'paham', 'Status harus paham');

        // IDOR check: user lain tidak boleh bisa membaca attempt ini
        assert_true(QuizEngine::getAttemptById($app, 999999, $resPass['attempt_id']) === null, 'IDOR attempt harus ditolak');
        assert_true(QuizEngine::getAttemptById($app, $uid, $resPass['attempt_id']) !== null, 'Pemilik harus bisa membaca attempt');

        // 2 benar, 3 salah -> 40% (< 75% => ulang)
        $failAnswers = [];
        foreach ($qs as $idx => $q) {
            $wrong = $q['correct_key'] === 'A' ? 'B' : 'A';
            $failAnswers[$q['id']] = ($idx < 2) ? $q['correct_key'] : $wrong;
        }
        $resFail = QuizEngine::evaluateAndSave($app, $uid, 1, $failAnswers, 15);
        assert_true($resFail['score_percent'] === 40.0, 'Skor 2/5 harus 40%');
        assert_true($resFail['passed'] === false, '40% harus tidak lulus');
        assert_true($resFail['status'] === 'ulang', 'Status harus ulang');

        $prog = $app->prepare('SELECT status, last_score FROM app_learning_progress WHERE user_id = :uid AND modul_no = 1');
        $prog->execute([':uid' => $uid]);
        $row = $prog->fetch();
        assert_true($row && $row['status'] === 'ulang' && (float) $row['last_score'] === 40.0, 'Progress terakhir harus ter-upsert');
    } finally {
        $del = $app->prepare('DELETE FROM app_users WHERE id = :id');
        $del->execute([':id' => $uid]);
    }
});

test_case('render_acrylic_code highlights SQL safely and escapes XSS', function (): void {
    $html = render_acrylic_code("SELECT * FROM Produk WHERE Nama = '<script>alert(1)</script>';", 'uji.sql', 'SQL');
    assert_true(str_contains($html, 'acrylic-code-card'), 'Harus memakai class acrylic-code-card');
    assert_true(str_contains($html, 'tok-keyword'), 'Harus menyorot keyword SQL');
    assert_true(!str_contains($html, '<script>'), 'Tag script mentah tidak boleh lolos');
    assert_true(str_contains($html, '&lt;script&gt;'), 'Tag script harus di-escape');
});

test_case('SimulatorSandbox runs all 11 whitelisted scenarios & resets cleanly', function (): void {
    assert_true(count(SimulatorSandbox::scenarios()) === 11, 'Harus ada 11 skenario whitelist');
    $bad = SimulatorSandbox::run('drop_all_tables');
    assert_true($bad['ok'] === false && !empty($bad['error']), 'Skenario tidak dikenal harus ditolak');

    SnapshotResetter::resetLabDatabase();
    try {
        $m1 = SimulatorSandbox::run('m1_norm_stepper', ['stage' => '3nf']);
        assert_true($m1['ok'] === true && ($m1['trace']['kind'] ?? '') === 'normalization', 'm1_norm_stepper harus mengembalikan fixture 3nf');

        $m2fk = SimulatorSandbox::run('m2_constraint_fk', ['nonota' => '9999999999', 'kode_produk' => 'P999']);
        assert_true($m2fk['ok'] === false && !empty($m2fk['error']), 'm2_constraint_fk harus menangkap pelanggaran FK');

        $m2uq = SimulatorSandbox::run('m2_constraint_uq', ['nama_produk' => 'Indomie goreng special']);
        assert_true($m2uq['ok'] === false && !empty($m2uq['error']), 'm2_constraint_uq harus menangkap pelanggaran UNIQUE');

        $m3j = SimulatorSandbox::run('m3_join_inner_left', ['join_type' => 'left']);
        assert_true($m3j['ok'] === true && ($m3j['trace']['kind'] ?? '') === 'venn' && count($m3j['after']) >= 4, 'm3_join_inner_left harus berhasil');

        $m3v = SimulatorSandbox::run('m3_view_select', ['view_name' => 'viewdetailtransaksi']);
        assert_true($m3v['ok'] === true && count($m3v['after']) >= 1, 'm3_view_select harus mengembalikan baris');

        $m3cf = SimulatorSandbox::run('m3_control_flow', ['func' => 'case']);
        assert_true($m3cf['ok'] === true && count($m3cf['after']) >= 1, 'm3_control_flow harus berhasil');

        $m4udf = SimulatorSandbox::run('m4_udf_cek_stok', ['kode_produk' => 'P001']);
        assert_true($m4udf['ok'] === true && (int) ($m4udf['after'][0]['cekstok'] ?? 0) === 97, 'fcCekStok P001 awal harus 97');

        $m4sp = SimulatorSandbox::run('m4_sp_bonus_point');
        assert_true($m4sp['ok'] === true && ($m4sp['trace']['kind'] ?? '') === 'cursor' && count($m4sp['trace']['rows']) >= 2, 'ProcBonusPoint harus menghasilkan cursor trace');

        $m5ins = SimulatorSandbox::run('m5_tg_insert_item', ['kode_produk' => 'P001', 'banyak' => 2]);
        assert_true($m5ins['ok'] === true, 'tg_produk_dijual insert harus berhasil');
        $p001AfterIns = array_values(array_filter($m5ins['after'], static fn (array $r): bool => $r['KodeProduk'] === 'P001'));
        assert_true((int) ($p001AfterIns[0]['stok'] ?? 0) === 95, 'Stok P001 harus berkurang 97 -> 95');

        $m5del = SimulatorSandbox::run('m5_tg_delete_item', ['nonota' => '2611130001', 'kode_produk' => 'P001']);
        assert_true($m5del['ok'] === true, 'tg_batal_jual delete harus berhasil');
        $p001AfterDel = array_values(array_filter($m5del['after'], static fn (array $r): bool => $r['KodeProduk'] === 'P001'));
        assert_true((int) ($p001AfterDel[0]['stok'] ?? 0) === 97, 'Stok P001 harus kembali 95 -> 97');

        $m5upd = SimulatorSandbox::run('m5_tg_update_kasir', ['kode_kasir' => '001', 'pass_baru' => 'tes123']);
        assert_true($m5upd['ok'] === true && count($m5upd['after']) >= 1, 'tg_update_kasir harus menulis logkasir');
    } finally {
        SnapshotResetter::resetLabDatabase();
    }
});

test_case('SimulatorSandbox::executePracticeQuery validates safety, SQL execution & muscle-memory match', function (): void {
    SnapshotResetter::resetLabDatabase();
    try {
        $blocked = SimulatorSandbox::executePracticeQuery('m1_norm_stepper', 'DROP TABLE produk;');
        assert_true($blocked['ok'] === false && $blocked['matched_instruction'] === false, 'DROP TABLE harus diblokir');

        $multi = SimulatorSandbox::executePracticeQuery('m1_norm_stepper', 'SELECT * FROM nota; DELETE FROM nota;');
        assert_true($multi['ok'] === false && $multi['matched_instruction'] === false, 'Multi-statement harus diblokir');

        $probe = SimulatorSandbox::executePracticeQuery('m1_norm_stepper', 'SELECT @@version, USER();');
        assert_true($probe['ok'] === false && $probe['matched_instruction'] === false, 'Probe @@version / USER() harus diblokir');

        $fk = SimulatorSandbox::executePracticeQuery('m2_constraint_fk', "INSERT INTO itemnota (Nonota, KodeProduk, banyak, hargajual) VALUES ('9999999999', 'P999', 1, 2500);");
        assert_true($fk['matched_instruction'] === true, 'Praktek FK harus mendeteksi error FK sebagai tepat');

        $join = SimulatorSandbox::executePracticeQuery('m3_join_inner_left', 'SELECT c.KodeCus AS Kode, n.Nonota FROM customer c LEFT JOIN nota n ON c.KodeCus = n.KodeCus;');
        assert_true($join['ok'] === true && $join['matched_instruction'] === true && ($join['trace']['kind'] ?? '') === 'venn', 'Praktek JOIN harus cocok & mengembalikan diagram Venn');

        $udf = SimulatorSandbox::executePracticeQuery('m4_udf_cek_stok', "SELECT fcCekStok('P001') AS cekstok;");
        assert_true($udf['ok'] === true && $udf['matched_instruction'] === true && (int) ($udf['after'][0]['cekstok'] ?? 0) === 97, 'Praktek UDF harus mengembalikan 97');

        $sp = SimulatorSandbox::executePracticeQuery('m4_sp_bonus_point', 'CALL ProcBonusPoint();');
        assert_true($sp['ok'] === true && $sp['matched_instruction'] === true && ($sp['trace']['kind'] ?? '') === 'cursor', 'Praktek SP harus mengembalikan cursor trace');

        $tg = SimulatorSandbox::executePracticeQuery('m5_tg_insert_item', "INSERT INTO itemnota (Nonota, KodeProduk, banyak, hargajual) VALUES ('2611130001', 'P001', 2, 2100);");
        assert_true($tg['ok'] === true && $tg['matched_instruction'] === true, 'Praktek trigger insert harus berhasil');
        $p001 = array_values(array_filter($tg['after'], static fn (array $r): bool => $r['KodeProduk'] === 'P001'));
        assert_true((int) ($p001[0]['stok'] ?? 0) === 95, 'Stok P001 harus turun menjadi 95');
    } finally {
        SnapshotResetter::resetLabDatabase();
    }
});

echo "\nSummary: {$passed} passed, {$failed} failed.\n";
exit($failed > 0 ? 1 : 0);
