<?php
declare(strict_types=1);

/**
 * Mengelola materi/knowledge tambahan mahasiswa per modul (app_custom_modules).
 */
final class CustomModuleService
{
    public static function listByUser(PDO $pdo, int $userId, ?int $modulNo = null): array
    {
        if ($modulNo !== null && $modulNo >= 1 && $modulNo <= 5) {
            $stmt = $pdo->prepare(
                'SELECT id, modul_no, judul, isi_materi, contoh_sql, created_at
                 FROM app_custom_modules
                 WHERE user_id = :uid AND modul_no = :m
                 ORDER BY created_at DESC, id DESC'
            );
            $stmt->execute([':uid' => $userId, ':m' => $modulNo]);
            return $stmt->fetchAll();
        }

        $stmt = $pdo->prepare(
            'SELECT id, modul_no, judul, isi_materi, contoh_sql, created_at
             FROM app_custom_modules
             WHERE user_id = :uid
             ORDER BY modul_no ASC, created_at DESC, id DESC'
        );
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    public static function create(
        PDO $pdo,
        int $userId,
        int $modulNo,
        string $judul,
        string $isiMateri,
        ?string $contohSql = null
    ): array {
        $judul = trim($judul);
        $isiMateri = trim($isiMateri);
        $contohSql = $contohSql !== null ? trim($contohSql) : '';

        if ($modulNo < 1 || $modulNo > 5) {
            return ['ok' => false, 'error' => 'Nomor modul harus antara 1 sampai 5.'];
        }
        if (mb_strlen($judul) < 3 || mb_strlen($judul) > 150) {
            return ['ok' => false, 'error' => 'Judul materi harus 3-150 karakter.'];
        }
        if ($isiMateri === '') {
            return ['ok' => false, 'error' => 'Isi materi/catatan wajib diisi.'];
        }

        $stmt = $pdo->prepare(
            'INSERT INTO app_custom_modules (user_id, modul_no, judul, isi_materi, contoh_sql)
             VALUES (:uid, :m, :j, :isi, :sql)'
        );
        $stmt->execute([
            ':uid' => $userId,
            ':m'   => $modulNo,
            ':j'   => $judul,
            ':isi' => $isiMateri,
            ':sql' => $contohSql !== '' ? $contohSql : null,
        ]);

        return ['ok' => true, 'id' => (int) $pdo->lastInsertId(), 'error' => null];
    }

    public static function delete(PDO $pdo, int $userId, int $noteId): bool
    {
        $stmt = $pdo->prepare(
            'DELETE FROM app_custom_modules WHERE id = :id AND user_id = :uid'
        );
        $stmt->execute([':id' => $noteId, ':uid' => $userId]);
        return $stmt->rowCount() > 0;
    }
}
