<?php
declare(strict_types=1);

/**
 * Mengambil ringkasan progres Modul 1-5 dan riwayat percobaan kuis mahasiswa.
 */
final class ProgressService
{
    public static function getDashboardSummary(PDO $pdo, int $userId): array
    {
        $catalog = require dirname(__DIR__) . '/data/modules_catalog.php';

        $stmt = $pdo->prepare(
            'SELECT modul_no, status, last_score, updated_at
             FROM app_learning_progress
             WHERE user_id = :uid'
        );
        $stmt->execute([':uid' => $userId]);
        $rows = $stmt->fetchAll();

        $progressByModul = [];
        foreach ($rows as $row) {
            $progressByModul[(int) $row['modul_no']] = $row;
        }

        $noteStmt = $pdo->prepare(
            'SELECT modul_no, COUNT(*) AS total_notes
             FROM app_custom_modules
             WHERE user_id = :uid
             GROUP BY modul_no'
        );
        $noteStmt->execute([':uid' => $userId]);
        $notesCount = [];
        foreach ($noteStmt->fetchAll() as $n) {
            $notesCount[(int) $n['modul_no']] = (int) $n['total_notes'];
        }

        $modules = [];
        $pahamCount = 0;
        foreach ($catalog as $no => $meta) {
            $prog = $progressByModul[$no] ?? [
                'status'     => 'belum',
                'last_score' => null,
                'updated_at' => null,
            ];
            if ($prog['status'] === 'paham') {
                $pahamCount++;
            }
            $modules[$no] = array_merge($meta, [
                'status'      => $prog['status'],
                'last_score'  => $prog['last_score'],
                'updated_at'  => $prog['updated_at'],
                'notes_count' => $notesCount[$no] ?? 0,
            ]);
        }

        $attemptStmt = $pdo->prepare(
            'SELECT modul_no, score_percent, correct_count, total_questions, created_at
             FROM app_quiz_attempts
             WHERE user_id = :uid
             ORDER BY created_at DESC
             LIMIT 5'
        );
        $attemptStmt->execute([':uid' => $userId]);

        return [
            'modules'         => $modules,
            'paham_count'     => $pahamCount,
            'total_modules'   => count($catalog),
            'recent_attempts' => $attemptStmt->fetchAll(),
        ];
    }
}
