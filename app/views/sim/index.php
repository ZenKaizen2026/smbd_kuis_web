<?php
declare(strict_types=1);

$renderDataTable = static function (array $rows, string $emptyMsg = 'Tidak ada baris data.'): void {
    if (empty($rows)) {
        echo '<p class="text-xs text-muted" style="margin:0;">' . e($emptyMsg) . '</p>';
        return;
    }
    $cols = array_keys($rows[0]);
    echo '<div style="overflow-x:auto;"><table style="width:100%;border-collapse:collapse;font-size:0.8125rem;">';
    echo '<thead><tr style="border-bottom:1px solid var(--color-border);text-align:left;background:#F8FAFC;">';
    foreach ($cols as $col) {
        echo '<th style="padding:0.45rem 0.6rem;white-space:nowrap;">' . e((string) $col) . '</th>';
    }
    echo '</tr></thead><tbody>';
    foreach ($rows as $row) {
        echo '<tr style="border-bottom:1px solid var(--color-border);">';
        foreach ($cols as $col) {
            $val = $row[$col] ?? null;
            echo '<td style="padding:0.45rem 0.6rem;white-space:nowrap;">'
                . ($val === null ? '<span class="text-muted font-mono">NULL</span>' : e((string) $val))
                . '</td>';
        }
        echo '</tr>';
    }
    echo '</tbody></table></div>';
};
?>
<main class="max-w-6xl w-full mx-auto p-6">
  <div class="mb-4 flex items-center justify-between gap-2 flex-wrap">
    <a href="dashboard.php" class="btn btn-outline btn-sm">&larr; Kembali ke Dashboard</a>
    <div class="flex items-center gap-2 flex-wrap">
      <?php foreach ($catalog as $m): ?>
        <?php $mNo = (int) $m['no']; ?>
        <a
          href="sim.php?modul=<?= $mNo ?>"
          class="btn btn-sm <?= $mNo === $modulNo ? 'btn-primary' : 'btn-outline' ?>"
        >
          Modul <?= $mNo ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <section class="card card-static card-floating p-6 mb-6">
    <div class="flex items-center justify-between gap-4 flex-wrap">
      <div>
        <span class="badge">Praktek Query Sandbox · Modul 0<?= $modulNo ?></span>
        <h1 class="text-2xl font-bold mt-2 mb-1"><?= e($activeModule['title']) ?></h1>
        <p class="text-sm text-muted" style="margin:0;">
          Ikuti instruksi dan ketik query SQL secara langsung pada <code class="font-mono">db_penjualan_lab</code> untuk melatih <em>muscle memory</em>.
        </p>
      </div>
      <div class="flex items-center gap-2 flex-wrap">
        <a href="quiz.php?modul=<?= $modulNo ?>" class="btn btn-outline btn-sm">Kuis Modul <?= $modulNo ?></a>
        <form method="POST" action="sim.php?modul=<?= $modulNo ?>&amp;scenario=<?= urlencode($selectedScenarioId) ?>" style="margin:0;">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="reset_snapshot">
          <input type="hidden" name="modul_no" value="<?= $modulNo ?>">
          <button type="submit" class="btn btn-danger-outline btn-sm">Reset Snapshot Lab</button>
        </form>
      </div>
    </div>
  </section>

  <?php if ($error !== null): ?>
    <div class="alert alert-error mb-4" role="alert"><?= e($error) ?></div>
  <?php endif; ?>

  <div class="sim-grid">
    <!-- Kolom Kiri: Daftar Misi Praktek & Referensi Normalisasi -->
    <section class="card card-floating p-6 mb-5">
      <h2 class="text-base font-bold mb-3" style="margin-top:0;">Misi Praktek Modul 0<?= $modulNo ?></h2>
      <div class="flex flex-col gap-3">
        <?php foreach ($modulScenarios as $idx => $s): ?>
          <?php $isActive = ($s['id'] === $selectedScenarioId); ?>
          <a
            href="sim.php?modul=<?= $modulNo ?>&amp;scenario=<?= urlencode($s['id']) ?>"
            style="display:block;padding:0.85rem;border-radius:0.75rem;border:1px solid <?= $isActive ? '#4F46E5' : 'var(--color-border)' ?>;background:<?= $isActive ? '#EEF2FF' : '#FFFFFF' ?>;text-decoration:none;color:inherit;transition:all 0.18s ease;"
          >
            <div class="flex items-center justify-between gap-2 mb-1">
              <span class="badge"><?= 'Latihan ' . ($idx + 1) ?></span>
              <?php if ($isActive): ?>
                <span class="text-xs font-semibold" style="color:#4F46E5;">Aktif</span>
              <?php endif; ?>
            </div>
            <div class="text-sm font-semibold" style="color:<?= $isActive ? '#312E81' : 'inherit' ?>;"><?= e($s['title']) ?></div>
            <div class="text-xs text-muted mt-1"><?= e($s['description']) ?></div>
          </a>
        <?php endforeach; ?>
      </div>

      <?php if ($modulNo === 1): ?>
        <div class="mt-6" style="border-top:1px solid var(--color-border);padding-top:1.1rem;">
          <h3 class="text-sm font-bold mb-2" style="margin-top:0;">Stepper Visual Normalisasi (UNF &rarr; 3NF)</h3>
          <p class="text-xs text-muted mb-3">Lihat evolusi tabel sebelum mempraktekkan query 3NF di samping.</p>
          <form method="POST" action="sim.php?modul=1&amp;scenario=m1_norm_stepper" class="flex items-center gap-2 flex-wrap">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="run_stepper">
            <?php foreach (['unf' => 'UNF', '1nf' => '1NF', '2nf' => '2NF', '3nf' => '3NF'] as $stKey => $stLabel): ?>
              <button
                type="submit"
                name="stage"
                value="<?= $stKey ?>"
                class="btn btn-sm <?= ($lastStage === $stKey && ($simResult['trace']['kind'] ?? '') === 'normalization') ? 'btn-primary' : 'btn-outline' ?>"
              >
                <?= $stLabel ?>
              </button>
            <?php endforeach; ?>
          </form>
        </div>
      <?php endif; ?>
    </section>

    <!-- Kolom Kanan: Instruksi, Acrylic Code Editor, & Hasil Eksekusi -->
    <section class="card card-floating p-6">
      <?php if ($selectedScenario !== null): ?>
        <div class="mb-4" style="background:#EEF2FF;border:1px solid #C7D2FE;border-radius:0.75rem;padding:1rem;">
          <div class="flex items-center justify-between gap-2 mb-1 flex-wrap">
            <span class="badge">Instruksi Praktek</span>
            <code class="text-xs font-mono" style="color:#3730A3;"><?= e($selectedScenario['schema_hint']) ?></code>
          </div>
          <p class="text-sm font-semibold mt-2 mb-0" style="color:#1E1B4B;"><?= e($selectedScenario['instruction']) ?></p>
        </div>

        <form method="POST" action="sim.php?modul=<?= $modulNo ?>&amp;scenario=<?= urlencode($selectedScenarioId) ?>" class="mb-5">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="run_practice">
          <input type="hidden" name="modul_no" value="<?= $modulNo ?>">
          <input type="hidden" name="scenario_id" value="<?= e($selectedScenarioId) ?>">

          <div class="acrylic-code-card mb-3">
            <div class="acrylic-code-header">
              <div class="acrylic-code-tabs">
                <span class="acrylic-code-tab active">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                    <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                    <path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"></path>
                  </svg>
                  <span>praktek_modul_<?= $modulNo ?>.sql</span>
                </span>
              </div>
              <div class="acrylic-code-actions">
                <span class="acrylic-code-lang">SQL</span>
                <button
                  type="button"
                  class="acrylic-code-copy"
                  data-hint-sql="<?= e($selectedScenario['hint_sql']) ?>"
                  onclick="fillPracticeHint(this)"
                  title="Isi contoh query jika lupa"
                >
                  Isi Contoh
                </button>
              </div>
            </div>
            <textarea
              id="user_sql_editor"
              name="user_sql"
              class="acrylic-code-textarea font-mono"
              rows="5"
              placeholder="-- Ketik query SQL Anda di sini sesuai instruksi di atas..."
              required
            ><?= e($lastUserSql) ?></textarea>
          </div>

          <div class="flex items-center justify-between gap-2 flex-wrap">
            <span class="text-xs text-muted">Tips: Biasakan mengetik sendiri tanpa menyalin untuk melatih muscle memory.</span>
            <button type="submit" class="btn btn-primary">Jalankan &amp; Cek Query</button>
          </div>
        </form>
      <?php endif; ?>

      <div style="border-top:1px solid var(--color-border);padding-top:1.25rem;">
        <h2 class="text-base font-bold mb-3" style="margin-top:0;">Hasil Eksekusi Sandbox</h2>

        <?php if ($simResult === null): ?>
          <p class="text-sm text-muted" style="margin:0;">
            Ketik query pada editor di atas lalu klik <strong>Jalankan &amp; Cek Query</strong> untuk melihat hasil nyata dari <code class="font-mono">db_penjualan_lab</code>.
          </p>
        <?php else: ?>
          <?php
            $matched = (bool) ($simResult['matched_instruction'] ?? $simResult['ok']);
            $feedbackText = (string) ($simResult['feedback'] ?? ($simResult['ok'] ? 'Eksekusi Berhasil' : 'Error Integritas / Penolakan DBMS'));
          ?>
          <div class="mb-3 flex items-center justify-between gap-2 flex-wrap">
            <span class="badge <?= $matched ? 'badge-paham' : 'badge-ulang' ?>">
              <?= e($feedbackText) ?>
            </span>
          </div>

          <?php if (!empty($simResult['sql_executed'])): ?>
            <?= render_acrylic_code((string) $simResult['sql_executed'], $selectedScenarioId . '.sql', 'SQL', 'mb-4') ?>
          <?php endif; ?>

          <?php if (!empty($simResult['error'])): ?>
            <div class="alert <?= $matched ? 'alert-success' : 'alert-error' ?> mb-4" role="alert">
              <strong>Respon MySQL:</strong> <code class="font-mono text-xs"><?= e((string) $simResult['error']) ?></code>
            </div>
          <?php endif; ?>

          <?php if (!empty($simResult['explanation'])): ?>
            <div class="text-sm mb-4" style="background:#F8FAFC;border:1px solid var(--color-border);border-radius:0.5rem;padding:0.75rem;">
              <strong>Penjelasan:</strong> <?= e((string) $simResult['explanation']) ?>
            </div>
          <?php endif; ?>

          <?php if (is_array($simResult['trace'] ?? null)): ?>
            <?php $trace = $simResult['trace']; ?>
            <?php if (($trace['kind'] ?? '') === 'normalization'): ?>
              <div class="mb-4">
                <div class="text-xs font-bold uppercase text-muted mb-2"><?= e((string) $trace['label']) ?></div>
                <?php foreach ($trace['tables'] as $tbl): ?>
                  <div class="mb-3" style="border:1px solid var(--color-border);border-radius:0.5rem;padding:0.75rem;">
                    <div class="text-xs font-bold mb-2"><?= e((string) $tbl['name']) ?></div>
                    <div style="overflow-x:auto;">
                      <table style="width:100%;border-collapse:collapse;font-size:0.8rem;">
                        <thead>
                          <tr style="background:#F8FAFC;border-bottom:1px solid var(--color-border);text-align:left;">
                            <?php foreach ($tbl['headers'] as $h): ?>
                              <th style="padding:0.4rem 0.55rem;white-space:nowrap;"><?= e((string) $h) ?></th>
                            <?php endforeach; ?>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($tbl['rows'] as $r): ?>
                            <tr style="border-bottom:1px solid var(--color-border);">
                              <?php foreach ($r as $cell): ?>
                                <td style="padding:0.4rem 0.55rem;"><?= e((string) $cell) ?></td>
                              <?php endforeach; ?>
                            </tr>
                          <?php endforeach; ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php elseif (($trace['kind'] ?? '') === 'venn'): ?>
              <?php $isLeft = (($trace['join_type'] ?? 'inner') === 'left'); ?>
              <div class="mb-4 p-4" style="background:#F8FAFC;border:1px solid var(--color-border);border-radius:0.5rem;text-align:center;">
                <svg width="240" height="120" viewBox="0 0 240 120" role="img" aria-label="Diagram Venn <?= $isLeft ? 'LEFT JOIN' : 'INNER JOIN' ?>">
                  <circle cx="90" cy="60" r="46" fill="<?= $isLeft ? 'rgba(79,70,229,0.28)' : 'rgba(148,163,184,0.12)' ?>" stroke="#4F46E5" stroke-width="2" />
                  <circle cx="150" cy="60" r="46" fill="rgba(148,163,184,0.12)" stroke="#0EA5E9" stroke-width="2" />
                  <path d="M120,25 A46,46 0 0,1 120,95 A46,46 0 0,1 120,25 Z" fill="rgba(79,70,229,0.45)" />
                  <text x="68" y="64" font-size="11" font-weight="600" fill="#1E1B4B">Customer</text>
                  <text x="150" y="64" font-size="11" font-weight="600" fill="#0369A1">Nota</text>
                </svg>
              </div>
            <?php elseif (($trace['kind'] ?? '') === 'cursor'): ?>
              <div class="mb-4">
                <div class="text-xs font-bold uppercase text-muted mb-2">Trace Iterasi CURSOR (bonusLoop)</div>
                <?php $renderDataTable($trace['rows'] ?? [], 'Tidak ada iterasi.'); ?>
              </div>
            <?php endif; ?>
          <?php endif; ?>

          <?php if (!empty($simResult['before'])): ?>
            <div class="mb-4">
              <div class="text-xs font-bold uppercase text-muted mb-2">Kondisi Before</div>
              <?php $renderDataTable($simResult['before']); ?>
            </div>
          <?php endif; ?>

          <?php if (!empty($simResult['after'])): ?>
            <div>
              <div class="text-xs font-bold uppercase text-muted mb-2">
                <?= !empty($simResult['before']) ? 'Kondisi After' : 'Hasil Query' ?>
              </div>
              <?php $renderDataTable($simResult['after']); ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </section>
  </div>
</main>
<script>
function fillPracticeHint(btn) {
  var editor = document.getElementById('user_sql_editor');
  if (editor) {
    editor.value = btn.getAttribute('data-hint-sql') || '';
    editor.focus();
  }
}
</script>
