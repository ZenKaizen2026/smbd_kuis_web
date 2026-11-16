<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/helpers/bootstrap.php';
require_once dirname(__DIR__) . '/app/services/QuizEngine.php';

$user = require_auth();
$pdo = app_pdo();
$catalog = require dirname(__DIR__) . '/app/data/modules_catalog.php';

$modulNo = isset($_GET['modul']) ? (int) $_GET['modul'] : 1;
if ($modulNo < 1 || $modulNo > 5) {
    $modulNo = 1;
}

$activeModule = null;
foreach ($catalog as $m) {
    if ((int) $m['no'] === $modulNo) {
        $activeModule = $m;
        break;
    }
}
if ($activeModule === null) {
    $activeModule = reset($catalog) ?: ['no' => 1, 'title' => 'Modul 1'];
    $modulNo = (int) $activeModule['no'];
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['_csrf_token'] ?? null)) {
        $error = 'Sesi formulir tidak valid. Silakan muat ulang halaman.';
    } else {
        $postModul = (int) ($_POST['modul_no'] ?? $modulNo);
        if ($postModul < 1 || $postModul > 5) {
            $postModul = $modulNo;
        }
        $startedAt = (int) ($_POST['started_at'] ?? time());
        $duration = max(0, time() - $startedAt);
        $submitted = is_array($_POST['answers'] ?? null) ? $_POST['answers'] : [];

        $questions = QuizEngine::loadQuestions($postModul);
        $unanswered = 0;
        foreach ($questions as $q) {
            $qid = (string) $q['id'];
            $val = isset($submitted[$qid]) ? trim((string) $submitted[$qid]) : '';
            if ($val === '') {
                $unanswered++;
            }
        }

        if ($unanswered > 0) {
            $error = "Masih ada {$unanswered} soal yang belum dipilih. Lengkapi semua jawaban sebelum mengirim.";
        } else {
            $result = QuizEngine::evaluateAndSave($pdo, (int) $user['id'], $postModul, $submitted, $duration);
            $msg = $result['passed']
                ? "Selamat! Skor Anda {$result['score_percent']}% (Paham — Lanjut)."
                : "Skor Anda {$result['score_percent']}% (Perlu Ulang — target 75%). Pelajari pembahasan di bawah.";
            set_flash($result['passed'] ? 'success' : 'error', $msg);
            redirect_to("quiz.php?modul={$postModul}&attempt={$result['attempt_id']}");
        }
    }
}

$reviewResult = null;
$attemptId = isset($_GET['attempt']) ? (int) $_GET['attempt'] : 0;
if ($attemptId > 0) {
    $reviewResult = QuizEngine::getAttemptById($pdo, (int) $user['id'], $attemptId);
    if ($reviewResult === null || (int) $reviewResult['modul_no'] !== $modulNo) {
        $reviewResult = null;
    }
}

$questions = QuizEngine::loadQuestions($modulNo);
$recentAttempts = QuizEngine::listRecentAttempts($pdo, (int) $user['id'], $modulNo, 5);
$startedTimestamp = time();

$pageTitle = "Kuis Modul {$modulNo}";
$activeNav = 'quiz';
$useAppShell = true;

require dirname(__DIR__) . '/app/views/layouts/header.php';
require dirname(__DIR__) . '/app/views/layouts/navbar.php';
require dirname(__DIR__) . '/app/views/quiz/index.php';
require dirname(__DIR__) . '/app/views/layouts/footer.php';
