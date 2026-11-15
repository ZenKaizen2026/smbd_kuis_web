<?php
declare(strict_types=1);

final class QuizEngine
{
    public const PASSING_THRESHOLD = 75.00;

    public static function loadQuestions(int $modulNo): array
    {
        if ($modulNo < 1 || $modulNo > 5) {
            throw new InvalidArgumentException('Nomor modul harus antara 1 sampai 5.');
        }
        $path = dirname(__DIR__) . "/data/quiz_modul{$modulNo}.php";
        if (!is_file($path)) {
            throw new RuntimeException("Bank soal modul {$modulNo} tidak ditemukan.");
        }
        $questions = require $path;
        return is_array($questions) ? $questions : [];
    }

    public static function evaluateAndSave(
        PDO $pdo,
        int $userId,
        int $modulNo,
        array $submittedAnswers,
        int $durationSeconds = 0
    ): array {
        $questions = self::loadQuestions($modulNo);
        $total = count($questions);
        $correct = 0;
        $answerDetails = [];
        $reviewItems = [];

        foreach ($questions as $q) {
            $qid = (string) $q['id'];
            $selectedRaw = $submittedAnswers[$qid] ?? null;
            $selected = is_string($selectedRaw) ? strtoupper(trim($selectedRaw)) : null;
            if ($selected === '' || !array_key_exists($selected, $q['options'])) {
                $selected = null;
            }

            $isCorrect = $selected !== null && $selected === $q['correct_key'];
            if ($isCorrect) {
                $correct++;
            }

            $answerDetails[$qid] = [
                'selected' => $selected,
                'is_correct' => $isCorrect,
            ];

            $reviewItems[] = [
                'id' => $qid,
                'type' => $q['type'],
                'question' => $q['question'],
                'code_snippet' => $q['code_snippet'] ?? null,
                'options' => $q['options'],
                'selected' => $selected,
                'correct_key' => $q['correct_key'],
                'is_correct' => $isCorrect,
                'explanation' => $q['explanation'],
            ];
        }

        $scorePercent = $total > 0 ? round(($correct / $total) * 100, 2) : 0.00;
        $passed = $scorePercent >= self::PASSING_THRESHOLD;
        $status = $passed ? 'paham' : 'ulang';
        $recommendation = $passed ? 'Lanjut' : 'Ulang';
        $duration = max(0, min($durationSeconds, 86400));

        $detailPayload = [
            'answers' => $answerDetails,
            'score_summary' => [
                'total' => $total,
                'correct' => $correct,
            ],
        ];
        $detailJson = json_encode($detailPayload, JSON_UNESCAPED_UNICODE);

        $pdo->beginTransaction();
        try {
            $insertAttempt = $pdo->prepare(
                'INSERT INTO app_quiz_attempts
                 (user_id, modul_no, score_percent, correct_count, total_questions, duration_seconds, detail_json)
                 VALUES (:uid, :modul, :score, :correct, :total, :dur, :detail)'
            );
            $insertAttempt->execute([
                ':uid' => $userId,
                ':modul' => $modulNo,
                ':score' => $scorePercent,
                ':correct' => $correct,
                ':total' => $total,
                ':dur' => $duration,
                ':detail' => $detailJson,
            ]);
            $attemptId = (int) $pdo->lastInsertId();

            $upsertProgress = $pdo->prepare(
                'INSERT INTO app_learning_progress (user_id, modul_no, status, last_score)
                 VALUES (:uid, :modul, :status, :score)
                 ON DUPLICATE KEY UPDATE status = VALUES(status), last_score = VALUES(last_score)'
            );
            $upsertProgress->execute([
                ':uid' => $userId,
                ':modul' => $modulNo,
                ':status' => $status,
                ':score' => $scorePercent,
            ]);

            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }

        return [
            'attempt_id' => $attemptId,
            'modul_no' => $modulNo,
            'score_percent' => $scorePercent,
            'correct_count' => $correct,
            'total_questions' => $total,
            'duration_seconds' => $duration,
            'passed' => $passed,
            'status' => $status,
            'recommendation' => $recommendation,
            'detail' => $detailPayload,
            'results' => $reviewItems,
        ];
    }

    public static function getAttemptById(PDO $pdo, int $userId, int $attemptId): ?array
    {
        $stmt = $pdo->prepare(
            'SELECT id, user_id, modul_no, score_percent, correct_count, total_questions, duration_seconds, detail_json, created_at
             FROM app_quiz_attempts
             WHERE id = :id AND user_id = :uid
             LIMIT 1'
        );
        $stmt->execute([':id' => $attemptId, ':uid' => $userId]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }

        $detail = json_decode((string) $row['detail_json'], true);
        $answers = is_array($detail['answers'] ?? null) ? $detail['answers'] : [];
        $questions = self::loadQuestions((int) $row['modul_no']);

        $reviewItems = [];
        foreach ($questions as $q) {
            $qid = (string) $q['id'];
            $ans = $answers[$qid] ?? ['selected' => null, 'is_correct' => false];
            $reviewItems[] = [
                'id' => $qid,
                'type' => $q['type'],
                'question' => $q['question'],
                'code_snippet' => $q['code_snippet'] ?? null,
                'options' => $q['options'],
                'selected' => $ans['selected'] ?? null,
                'correct_key' => $q['correct_key'],
                'is_correct' => (bool) ($ans['is_correct'] ?? false),
                'explanation' => $q['explanation'],
            ];
        }

        $scorePercent = (float) $row['score_percent'];
        $passed = $scorePercent >= self::PASSING_THRESHOLD;

        return [
            'attempt_id' => (int) $row['id'],
            'modul_no' => (int) $row['modul_no'],
            'score_percent' => $scorePercent,
            'correct_count' => (int) $row['correct_count'],
            'total_questions' => (int) $row['total_questions'],
            'duration_seconds' => (int) $row['duration_seconds'],
            'created_at' => (string) $row['created_at'],
            'passed' => $passed,
            'status' => $passed ? 'paham' : 'ulang',
            'recommendation' => $passed ? 'Lanjut' : 'Ulang',
            'results' => $reviewItems,
        ];
    }

    public static function listRecentAttempts(PDO $pdo, int $userId, int $modulNo, int $limit = 5): array
    {
        $limit = max(1, min($limit, 20));
        $stmt = $pdo->prepare(
            "SELECT id, modul_no, score_percent, correct_count, total_questions, duration_seconds, created_at
             FROM app_quiz_attempts
             WHERE user_id = :uid AND modul_no = :modul
             ORDER BY id DESC
             LIMIT {$limit}"
        );
        $stmt->execute([':uid' => $userId, ':modul' => $modulNo]);
        return $stmt->fetchAll();
    }
}
