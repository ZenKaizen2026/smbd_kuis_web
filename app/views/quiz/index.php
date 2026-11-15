<?php
declare(strict_types=1);
$typeLabels = [
    'mcq' => 'Konsep / MCQ',
    'output_trace' => 'Trace Output',
    'syntax_debug' => 'Debug Sintaks',
];
?>
<main class="max-w-5xl w-full mx-auto p-6">
  <div class="mb-4 flex items-center justify-between gap-2 flex-wrap">
    <a href="dashboard.php" class="btn btn-outline btn-sm">&larr; Kembali ke Dashboard</a>
    <div class="flex items-center gap-2 flex-wrap">
      <?php foreach ($catalog as $m): ?>
        <?php $mNo = (int) $m['no']; ?>
        <a
          href="quiz.php?modul=<?= $mNo ?>"
          class="btn btn-sm <?= $mNo === $modulNo ? 'btn-primary' : 'btn-outline' ?>"
        >
          Modul <?= $mNo ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <section class="card card-static p-6 mb-6">
    <div class="flex items-center justify-between gap-4 flex-wrap">
      <div>
        <span class="badge">Kuis Evaluasi · Modul 0<?= $modulNo ?></span>
        <h1 class="text-2xl font-bold mt-2 mb-1"><?= e($activeModule['title']) ?></h1>
        <p class="text-sm text-muted" style="margin:0;">
          <?= count($questions) ?> soal · Ambang lulus: <strong>75%</strong> (&ge; 4 jawaban benar) untuk status <strong>Paham</strong>.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <a href="sim.php?modul=<?= $modulNo ?>" class="btn btn-outline btn-sm">Simulasi Lab Modul <?= $modulNo ?></a>
      </div>
    </div>
  </section>

  <?php if ($error !== null): ?>
    <div class="alert alert-error mb-4" role="alert"><?= e($error) ?></div>
  <?php endif; ?>

  <?php if ($reviewResult !== null): ?>
    <?php
      $isPassed = $reviewResult['passed'];
      $badgeCls = $isPassed ? 'badge-paham' : 'badge-ulang';
      $nextModul = $modulNo < 5 ? $modulNo + 1 : 1;
    ?>
    <section class="card card-static p-6 mb-6" style="border-left:4px solid <?= $isPassed ? '#10B981' : '#F59E0B' ?>;">
      <div class="flex items-center justify-between gap-4 flex-wrap">
        <div>
          <span class="badge <?= $badgeCls ?>">
            <?= $isPassed ? 'Lulus · Paham (Lanjut)' : 'Belum Lulus · Perlu Ulang' ?>
          </span>
          <h2 class="text-xl font-bold mt-2 mb-1">
            Skor Anda: <?= number_format((float) $reviewResult['score_percent'], 0) ?>%
            (<?= (int) $reviewResult['correct_count'] ?> / <?= (int) $reviewResult['total_questions'] ?> Benar)
          </h2>
          <p class="text-xs text-muted" style="margin:0;">
            Durasi pengerjaan: <?= (int) $reviewResult['duration_seconds'] ?> detik · Disimpan ke progres belajar Anda.
          </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
          <a href="quiz.php?modul=<?= $modulNo ?>" class="btn btn-outline btn-sm">Ulangi Kuis</a>
          <?php if ($isPassed && $modulNo < 5): ?>
            <a href="quiz.php?modul=<?= $nextModul ?>" class="btn btn-primary btn-sm">Lanjut ke Modul <?= $nextModul ?> &rarr;</a>
          <?php else: ?>
            <a href="sim.php?modul=<?= $modulNo ?>" class="btn btn-primary btn-sm">Latihan di Simulasi Lab</a>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <section class="mb-6" aria-label="Pembahasan Jawaban">
      <?php foreach ($reviewResult['results'] as $idx => $item): ?>
        <article class="card card-static p-6 mb-4">
          <div class="flex items-center justify-between gap-2 mb-2 flex-wrap">
            <div class="flex items-center gap-2">
              <span class="badge">Soal <?= $idx + 1 ?></span>
              <span class="text-xs text-muted"><?= e($typeLabels[$item['type']] ?? $item['type']) ?></span>
            </div>
            <span class="badge <?= $item['is_correct'] ? 'badge-paham' : 'badge-ulang' ?>">
              <?= $item['is_correct'] ? 'Benar' : 'Salah' ?>
            </span>
          </div>

          <p class="font-semibold mb-3" style="margin-top:0;"><?= e($item['question']) ?></p>

          <?php if (!empty($item['code_snippet'])): ?>
            <?= render_acrylic_code((string) $item['code_snippet'], 'modul_' . $modulNo . '_soal_' . ($idx + 1) . '.sql', 'SQL', 'mb-3') ?>
          <?php endif; ?>

          <div class="mb-3" style="display:flex;flex-direction:column;gap:0.4rem;">
            <?php foreach ($item['options'] as $optKey => $optText): ?>
              <?php
                $isKey = ($optKey === $item['correct_key']);
                $isPicked = ($optKey === $item['selected']);
                $rowStyle = 'padding:0.55rem 0.75rem;border-radius:0.5rem;border:1px solid var(--color-border);font-size:0.875rem;';
                if ($isKey) {
                    $rowStyle .= 'background:#ECFDF5;border-color:#10B981;color:#065F46;font-weight:600;';
                } elseif ($isPicked && !$isKey) {
                    $rowStyle .= 'background:#FEF2F2;border-color:#EF4444;color:#991B1B;';
                }
              ?>
              <div style="<?= $rowStyle ?>">
                <span class="font-mono font-bold"><?= e((string) $optKey) ?>.</span>
                <?= e((string) $optText) ?>
                <?php if ($isKey): ?>
                  <span class="text-xs" style="margin-left:0.5rem;">(Kunci)</span>
                <?php elseif ($isPicked): ?>
                  <span class="text-xs" style="margin-left:0.5rem;">(Pilihan Anda)</span>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="text-sm" style="background:#F8FAFC;border:1px solid var(--color-border);border-radius:0.5rem;padding:0.75rem;">
            <strong>Pembahasan:</strong> <?= e($item['explanation']) ?>
          </div>
        </article>
      <?php endforeach; ?>
    </section>

  <?php else: ?>
    <form method="POST" action="quiz.php?modul=<?= $modulNo ?>" class="mb-6">
      <?= csrf_field() ?>
      <input type="hidden" name="modul_no" value="<?= $modulNo ?>">
      <input type="hidden" name="started_at" value="<?= $startedTimestamp ?>">

      <?php foreach ($questions as $idx => $q): ?>
        <?php
          $qid = (string) $q['id'];
          $prevSelected = isset($_POST['answers'][$qid]) ? strtoupper((string) $_POST['answers'][$qid]) : '';
        ?>
        <article class="card card-static p-6 mb-4">
          <div class="flex items-center justify-between gap-2 mb-2 flex-wrap">
            <span class="badge">Soal <?= $idx + 1 ?> dari <?= count($questions) ?></span>
            <span class="text-xs text-muted"><?= e($typeLabels[$q['type']] ?? $q['type']) ?></span>
          </div>

          <h2 class="text-base font-semibold mb-3" style="margin-top:0;"><?= e($q['question']) ?></h2>

          <?php if (!empty($q['code_snippet'])): ?>
            <?= render_acrylic_code((string) $q['code_snippet'], 'modul_' . $modulNo . '_soal_' . ($idx + 1) . '.sql', 'SQL', 'mb-3') ?>
          <?php endif; ?>

          <div style="display:flex;flex-direction:column;gap:0.5rem;">
            <?php foreach ($q['options'] as $optKey => $optText): ?>
              <label style="display:flex;align-items:flex-start;gap:0.6rem;padding:0.6rem 0.75rem;border:1px solid var(--color-border);border-radius:0.5rem;cursor:pointer;font-size:0.875rem;">
                <input
                  type="radio"
                  name="answers[<?= e($qid) ?>]"
                  value="<?= e((string) $optKey) ?>"
                  <?= $prevSelected === $optKey ? 'checked' : '' ?>
                  required
                  style="margin-top:0.2rem;"
                >
                <span><strong class="font-mono"><?= e((string) $optKey) ?>.</strong> <?= e((string) $optText) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </article>
      <?php endforeach; ?>

      <div class="flex items-center justify-end gap-3">
        <button type="submit" class="btn btn-primary">Kirim Jawaban &amp; Lihat Pembahasan</button>
      </div>
    </form>
  <?php endif; ?>

  <?php if (!empty($recentAttempts)): ?>
    <section class="card card-static p-6">
      <h2 class="text-base font-bold mb-3" style="margin-top:0;">Riwayat Percobaan Modul 0<?= $modulNo ?></h2>
      <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:0.875rem;">
          <thead>
            <tr style="border-bottom:1px solid var(--color-border);text-align:left;">
              <th style="padding:0.5rem;">Waktu</th>
              <th style="padding:0.5rem;">Skor</th>
              <th style="padding:0.5rem;">Benar</th>
              <th style="padding:0.5rem;">Durasi</th>
              <th style="padding:0.5rem;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($recentAttempts as $att): ?>
              <tr style="border-bottom:1px solid var(--color-border);">
                <td style="padding:0.5rem;"><?= e((string) $att['created_at']) ?></td>
                <td style="padding:0.5rem;font-weight:600;"><?= number_format((float) $att['score_percent'], 0) ?>%</td>
                <td style="padding:0.5rem;"><?= (int) $att['correct_count'] ?> / <?= (int) $att['total_questions'] ?></td>
                <td style="padding:0.5rem;"><?= (int) $att['duration_seconds'] ?> dtk</td>
                <td style="padding:0.5rem;">
                  <a href="quiz.php?modul=<?= $modulNo ?>&attempt=<?= (int) $att['id'] ?>" class="btn btn-outline btn-sm">Lihat Pembahasan</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  <?php endif; ?>
</main>
