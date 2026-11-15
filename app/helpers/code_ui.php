<?php
declare(strict_types=1);

/**
 * Menyorot sintaks SQL secara aman (XSS-safe) dalam satu pass regex.
 */
function highlight_sql_safe(string $code): string
{
    $escaped = htmlspecialchars($code, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $keywords = 'SELECT|FROM|WHERE|INNER|LEFT|RIGHT|OUTER|JOIN|ON|GROUP|BY|ORDER|ASC|DESC|HAVING|LIMIT|DISTINCT|'
        . 'INSERT|INTO|VALUES|UPDATE|SET|DELETE|CREATE|ALTER|DROP|TABLE|VIEW|TRIGGER|PROCEDURE|FUNCTION|'
        . 'BEGIN|END|DECLARE|CURSOR|OPEN|FETCH|CLOSE|IF|ELSE|ELSEIF|CASE|WHEN|THEN|RETURN|CALL|'
        . 'PRIMARY|KEY|FOREIGN|REFERENCES|UNIQUE|CHECK|NOT|NULL|DEFAULT|AS|AND|OR|IN|IS|LIKE|BETWEEN|'
        . 'BEFORE|AFTER|FOR|EACH|ROW|NEW|OLD|HANDLER|CONTINUE|FOUND|LEAVE|LOOP|INT|CHAR|VARCHAR|DECIMAL|DATE|DATETIME';

    $pattern = '/(--[^\r\n]*|\'[^\']*\'|"[^"]*"|\b(?:' . $keywords . ')\b|\b[A-Za-z_][A-Za-z0-9_]*(?=\s*\()|\b\d+(?:\.\d+)?\b)/i';

    return (string) preg_replace_callback($pattern, static function (array $m): string {
        $tok = $m[0];
        if (str_starts_with($tok, '--')) {
            return '<span class="tok-comment">' . $tok . '</span>';
        }
        if ($tok[0] === '\'' || $tok[0] === '"') {
            return '<span class="tok-string">' . $tok . '</span>';
        }
        if (is_numeric($tok)) {
            return '<span class="tok-number">' . $tok . '</span>';
        }
        if (preg_match('/^(SELECT|FROM|WHERE|INNER|LEFT|RIGHT|OUTER|JOIN|ON|GROUP|BY|ORDER|ASC|DESC|HAVING|LIMIT|DISTINCT|INSERT|INTO|VALUES|UPDATE|SET|DELETE|CREATE|ALTER|DROP|TABLE|VIEW|TRIGGER|PROCEDURE|FUNCTION|BEGIN|END|DECLARE|CURSOR|OPEN|FETCH|CLOSE|IF|ELSE|ELSEIF|CASE|WHEN|THEN|RETURN|CALL|PRIMARY|KEY|FOREIGN|REFERENCES|UNIQUE|CHECK|NOT|NULL|DEFAULT|AS|AND|OR|IN|IS|LIKE|BETWEEN|BEFORE|AFTER|FOR|EACH|ROW|NEW|OLD|HANDLER|CONTINUE|FOUND|LEAVE|LOOP|INT|CHAR|VARCHAR|DECIMAL|DATE|DATETIME)$/i', $tok)) {
            return '<span class="tok-keyword">' . $tok . '</span>';
        }
        return '<span class="tok-func">' . $tok . '</span>';
    }, $escaped);
}

/**
 * Merender blok kode/query bergaya Acrylic Code Editor (dark glass + tab bar + line numbers).
 */
function render_acrylic_code(string $code, string $filename = 'query.sql', string $lang = 'SQL', string $extraClass = ''): string
{
    $normalized = str_replace(["\r\n", "\r"], "\n", rtrim($code, "\r\n"));
    $lines = explode("\n", $normalized);
    $lineNumsHtml = '';
    foreach ($lines as $idx => $_line) {
        $lineNumsHtml .= '<span>' . ($idx + 1) . '</span>';
    }
    $highlighted = highlight_sql_safe($normalized);
    $copyPayload = htmlspecialchars($normalized, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $safeFile = htmlspecialchars($filename, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $safeLang = htmlspecialchars($lang, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $cls = trim('acrylic-code-card ' . $extraClass);

    return <<<HTML
<div class="{$cls}">
  <div class="acrylic-code-header">
    <div class="acrylic-code-tabs">
      <span class="acrylic-code-tab active">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
          <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
          <path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"></path>
        </svg>
        <span>{$safeFile}</span>
      </span>
    </div>
    <div class="acrylic-code-actions">
      <span class="acrylic-code-lang">{$safeLang}</span>
      <button type="button" class="acrylic-code-copy" data-copy-code="{$copyPayload}" onclick="copyAcrylicCode(this)" title="Salin query">Salin</button>
    </div>
  </div>
  <div class="acrylic-code-body">
    <div class="acrylic-code-lines" aria-hidden="true">{$lineNumsHtml}</div>
    <pre class="acrylic-code-content font-mono"><code>{$highlighted}</code></pre>
  </div>
</div>
HTML;
}
