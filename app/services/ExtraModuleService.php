<?php
declare(strict_types=1);

/**
 * Memproses unggahan modul (.md / .pdf) khusus admin menjadi materi siap baca di Dashboard.
 */
final class ExtraModuleService
{
    private const MAX_BYTES = 5242880; // 5 MB

    public static function uploadDir(): string
    {
        return dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'modules';
    }

    public static function listAll(PDO $pdo): array
    {
        $stmt = $pdo->query(
            'SELECT id, modul_code, judul, ringkasan, topik_json, source_type, original_filename, created_at
             FROM app_extra_modules
             ORDER BY id DESC'
        );
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) {
            $decoded = json_decode((string) ($r['topik_json'] ?? '[]'), true);
            $r['topics'] = is_array($decoded) ? $decoded : [];
        }
        return $rows;
    }

    public static function findById(PDO $pdo, int $id): ?array
    {
        $stmt = $pdo->prepare(
            'SELECT id, modul_code, judul, ringkasan, topik_json, konten_html, source_type, stored_filename, original_filename, created_at
             FROM app_extra_modules
             WHERE id = :id
             LIMIT 1'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if (!$row) {
            return null;
        }
        $decoded = json_decode((string) ($row['topik_json'] ?? '[]'), true);
        $row['topics'] = is_array($decoded) ? $decoded : [];
        return $row;
    }

    public static function processAndCreate(
        PDO $pdo,
        int $adminUserId,
        array $file,
        string $modulCode,
        string $customTitle = ''
    ): array {
        $modulCode = trim($modulCode);
        $customTitle = trim($customTitle);

        if ($modulCode === '' || mb_strlen($modulCode) > 30) {
            return ['ok' => false, 'error' => 'Kode modul wajib diisi (maks. 30 karakter, mis. Modul 06).'];
        }
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'error' => 'File gagal diunggah. Pastikan memilih file .md atau .pdf.'];
        }
        if (($file['size'] ?? 0) <= 0 || ($file['size'] ?? 0) > self::MAX_BYTES) {
            return ['ok' => false, 'error' => 'Ukuran file harus antara 1 byte sampai 5 MB.'];
        }

        $origName = basename((string) ($file['name'] ?? 'modul'));
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        if (!in_array($ext, ['md', 'pdf'], true)) {
            return ['ok' => false, 'error' => 'Format file hanya mendukung .md atau .pdf.'];
        }

        $tmpPath = (string) ($file['tmp_name'] ?? '');
        if ($tmpPath === '' || !is_file($tmpPath)) {
            return ['ok' => false, 'error' => 'File sementara unggahan tidak ditemukan.'];
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($tmpPath);
        if ($ext === 'pdf' && $mime !== 'application/pdf') {
            return ['ok' => false, 'error' => 'File .pdf tidak valid (MIME: ' . $mime . ').'];
        }
        if ($ext === 'md' && !str_starts_with($mime, 'text/') && $mime !== 'application/octet-stream') {
            return ['ok' => false, 'error' => 'File .md harus berupa teks Markdown yang valid.'];
        }

        $dir = self::uploadDir();
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $storedName = bin2hex(random_bytes(16)) . '.' . $ext;
        $destPath = $dir . DIRECTORY_SEPARATOR . $storedName;
        if (!move_uploaded_file($tmpPath, $destPath)) {
            if (!@copy($tmpPath, $destPath)) {
                return ['ok' => false, 'error' => 'Gagal menyimpan file ke storage.'];
            }
        }

        $parsed = $ext === 'md'
            ? self::parseMarkdown((string) file_get_contents($destPath), $customTitle, $origName)
            : self::parsePdf($destPath, $customTitle, $origName);

        $stmt = $pdo->prepare(
            'INSERT INTO app_extra_modules
             (created_by, modul_code, judul, ringkasan, topik_json, konten_html, source_type, stored_filename, original_filename)
             VALUES (:uid, :code, :judul, :ringkasan, :topik, :html, :stype, :stored, :orig)'
        );
        $stmt->execute([
            ':uid'       => $adminUserId,
            ':code'      => $modulCode,
            ':judul'     => $parsed['title'],
            ':ringkasan' => $parsed['summary'],
            ':topik'     => json_encode($parsed['topics'], JSON_UNESCAPED_UNICODE),
            ':html'      => $parsed['html'],
            ':stype'     => $ext,
            ':stored'    => $storedName,
            ':orig'      => mb_substr($origName, 0, 255),
        ]);

        return ['ok' => true, 'id' => (int) $pdo->lastInsertId(), 'error' => null];
    }

    public static function delete(PDO $pdo, int $id): bool
    {
        $row = self::findById($pdo, $id);
        if (!$row) {
            return false;
        }
        $stmt = $pdo->prepare('DELETE FROM app_extra_modules WHERE id = :id');
        $stmt->execute([':id' => $id]);

        $filePath = self::uploadDir() . DIRECTORY_SEPARATOR . basename($row['stored_filename']);
        if (is_file($filePath)) {
            @unlink($filePath);
        }
        return $stmt->rowCount() > 0;
    }

    /**
     * Parser Markdown aman (XSS-safe): mengekstrak judul, topik, ringkasan, dan HTML terformat.
     */
    public static function parseMarkdown(string $raw, string $customTitle = '', string $fallbackName = 'Modul Tambahan'): array
    {
        $lines = preg_split("/\r\n|\n|\r/", $raw) ?: [];
        $title = $customTitle;
        $topics = [];
        $summary = '';
        $htmlParts = [];
        $inCode = false;
        $codeBuffer = [];
        $inList = false;

        $closeList = static function () use (&$inList, &$htmlParts): void {
            if ($inList) {
                $htmlParts[] = '</ul>';
                $inList = false;
            }
        };

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if (str_starts_with($trimmed, '```')) {
                $closeList();
                if ($inCode) {
                    $codeText = htmlspecialchars(implode("\n", $codeBuffer), ENT_QUOTES, 'UTF-8');
                    $htmlParts[] = '<pre class="sql-panel font-mono" style="margin:0.75rem 0;"><code>' . $codeText . '</code></pre>';
                    $codeBuffer = [];
                    $inCode = false;
                } else {
                    $inCode = true;
                }
                continue;
            }

            if ($inCode) {
                $codeBuffer[] = $line;
                continue;
            }

            if ($trimmed === '') {
                $closeList();
                continue;
            }

            if (preg_match('/^#\s+(.+)$/', $trimmed, $m)) {
                $closeList();
                if ($title === '') {
                    $title = trim($m[1]);
                }
                $htmlParts[] = '<h2 class="text-xl font-bold mt-4 mb-2">' . self::formatInline($m[1]) . '</h2>';
            } elseif (preg_match('/^##\s+(.+)$/', $trimmed, $m)) {
                $closeList();
                if (count($topics) < 5) {
                    $topics[] = mb_substr(trim($m[1]), 0, 40);
                }
                $htmlParts[] = '<h3 class="text-lg font-bold mt-4 mb-2">' . self::formatInline($m[1]) . '</h3>';
            } elseif (preg_match('/^###\s+(.+)$/', $trimmed, $m)) {
                $closeList();
                $htmlParts[] = '<h4 class="text-base font-semibold mt-3 mb-1">' . self::formatInline($m[1]) . '</h4>';
            } elseif (preg_match('/^[-*]\s+(.+)$/', $trimmed, $m)) {
                if (!$inList) {
                    $htmlParts[] = '<ul style="margin:0.5rem 0 0.75rem 1.25rem;padding:0;line-height:1.6;">';
                    $inList = true;
                }
                $htmlParts[] = '<li class="text-sm text-muted" style="margin-bottom:0.25rem;">' . self::formatInline($m[1]) . '</li>';
            } else {
                $closeList();
                if ($summary === '' && mb_strlen($trimmed) > 10) {
                    $summary = mb_substr($trimmed, 0, 260);
                }
                $htmlParts[] = '<p class="text-sm text-muted" style="margin:0.4rem 0;line-height:1.65;">' . self::formatInline($trimmed) . '</p>';
            }
        }

        $closeList();
        if ($inCode && $codeBuffer !== []) {
            $codeText = htmlspecialchars(implode("\n", $codeBuffer), ENT_QUOTES, 'UTF-8');
            $htmlParts[] = '<pre class="sql-panel font-mono"><code>' . $codeText . '</code></pre>';
        }

        if ($title === '') {
            $title = pathinfo($fallbackName, PATHINFO_FILENAME);
        }
        if ($summary === '') {
            $summary = 'Materi tambahan terstruktur dari berkas Markdown (' . $fallbackName . ').';
        }
        if ($topics === []) {
            $topics = ['Materi Tambahan'];
        }

        return [
            'title'   => mb_substr($title, 0, 150),
            'summary' => mb_substr($summary, 0, 280),
            'topics'  => $topics,
            'html'    => implode("\n", $htmlParts),
        ];
    }

    private static function formatInline(string $text): string
    {
        $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $escaped = preg_replace('/`([^`]+)`/', '<code class="font-mono" style="background:#EEF2FF;padding:0.1rem 0.35rem;border-radius:0.25rem;">$1</code>', $escaped) ?? $escaped;
        $escaped = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $escaped) ?? $escaped;
        return $escaped;
    }

    private static function parsePdf(string $filePath, string $customTitle, string $origName): array
    {
        $extracted = '';
        $nullDevice = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';
        $cmd = 'pdftotext -layout ' . escapeshellarg($filePath) . ' - 2>' . $nullDevice;
        $output = @shell_exec($cmd);
        if (is_string($output) && trim($output) !== '') {
            $extracted = trim($output);
        }

        $title = $customTitle !== '' ? $customTitle : pathinfo($origName, PATHINFO_FILENAME);

        if ($extracted === '') {
            return [
                'title'   => mb_substr($title, 0, 150),
                'summary' => 'Dokumen PDF siap dibaca langsung melalui penampil PDF terintegrasi.',
                'topics'  => ['Dokumen PDF'],
                'html'    => '<p class="text-sm text-muted">Teks PDF tidak dapat diekstrak otomatis (kemungkinan PDF hasil scan). Gunakan penampil PDF di bawah untuk membaca materi.</p>',
            ];
        }

        $cleanText = preg_replace("/\n{3,}/", "\n\n", $extracted) ?? $extracted;
        $paragraphs = array_filter(array_map('trim', explode("\n\n", $cleanText)), static fn(string $p): bool => $p !== '');
        $first = reset($paragraphs);
        $summary = is_string($first) ? mb_substr(preg_replace('/\s+/', ' ', $first) ?? $first, 0, 260) : 'Materi PDF terproses.';

        $htmlParts = [];
        $count = 0;
        foreach ($paragraphs as $p) {
            if ($count >= 40) {
                $htmlParts[] = '<p class="text-xs text-muted"><em>(Teks ringkasan dipotong; buka penampil PDF di bawah untuk halaman lengkap.)</em></p>';
                break;
            }
            $htmlParts[] = '<p class="text-sm text-muted" style="margin:0.5rem 0;white-space:pre-line;line-height:1.6;">' . htmlspecialchars($p, ENT_QUOTES, 'UTF-8') . '</p>';
            $count++;
        }

        return [
            'title'   => mb_substr($title, 0, 150),
            'summary' => mb_substr($summary, 0, 280),
            'topics'  => ['Dokumen PDF', 'Ekstrak Teks'],
            'html'    => implode("\n", $htmlParts),
        ];
    }
}
