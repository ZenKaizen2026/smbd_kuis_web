<?php
declare(strict_types=1);
$pct = $summary['total_modules'] > 0
    ? (int) round(($summary['paham_count'] / $summary['total_modules']) * 100)
    : 0;
?>
<main class="max-w-6xl w-full mx-auto p-6">
  <section class="card card-static p-6 mb-6">
    <div class="flex items-center justify-between gap-4 flex-wrap">
      <div>
        <span class="badge">Progres Belajar Modul 1–5</span>
        <h1 class="text-2xl font-bold mt-2 mb-1">Selamat Belajar, <?= e($user['nama_lengkap']) ?></h1>
        <p class="text-sm text-muted" style="margin:0;">
          Kuasai Normalisasi hingga Trigger berbasis studi kasus <code class="font-mono">db_penjualan</code>. Target lulus kuis per modul: <strong>75%</strong>.
        </p>
      </div>
      <div style="min-width:220px;">
        <div class="flex items-center justify-between text-sm font-semibold mb-1">
          <span>Penguasaan</span>
          <span><?= $summary['paham_count'] ?> / <?= $summary['total_modules'] ?> Modul (<?= $pct ?>%)</span>
        </div>
        <div class="progress-track" role="progressbar" aria-valuenow="<?= $pct ?>" aria-valuemin="0" aria-valuemax="100">
          <div class="progress-fill" style="width: <?= $pct ?>%;"></div>
        </div>
      </div>
    </div>
  </section>

  <section aria-label="Daftar Modul Pembelajaran">
    <div class="grid grid-3 gap-4" style="gap:1.25rem;">
      <?php foreach ($summary['modules'] as $m): ?>
        <?php
          $statusClass = match ($m['status']) {
              'paham' => 'badge-paham',
              'ulang' => 'badge-ulang',
              default => 'badge-belum',
          };
          $statusLabel = match ($m['status']) {
              'paham' => 'Paham',
              'ulang' => 'Perlu Ulang',
              default => 'Belum',
          };
          $no = (int) $m['no'];
        ?>
        <article class="card module-card">
          <div class="module-banner module-banner--<?= $no ?>">
            <span class="pill-glass">Modul 0<?= $no ?></span>
            <div class="flex items-center gap-2" style="position:relative;z-index:2;">
              <span class="badge <?= $statusClass ?>"><?= $statusLabel ?></span>
              <a href="materi.php?modul=<?= $no ?>" class="circle-icon-btn" title="Catatan &amp; Materi Tambahan Modul <?= $no ?>" aria-label="Catatan Modul <?= $no ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                </svg>
              </a>
            </div>
            <svg class="module-banner-art" width="96" height="96" viewBox="0 0 24 24" fill="none" stroke="#312E81" stroke-width="1.3" aria-hidden="true">
              <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
              <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
              <path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"></path>
            </svg>
          </div>

          <div class="module-body">
            <h2 class="text-lg font-bold" style="margin:0 0 0.25rem;"><?= e($m['title']) ?></h2>
            <p class="text-xs text-muted" style="margin:0;">SI167 · Studi Kasus <code class="font-mono">db_penjualan</code></p>

            <div class="meta-strip">
              <div class="meta-cell">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <circle cx="12" cy="8" r="7"></circle>
                  <polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline>
                </svg>
                <span class="meta-label"><?= $m['last_score'] !== null ? e((string) $m['last_score']) . '%' : 'Belum' ?></span>
              </div>
              <div class="meta-cell" title="<?= e(implode(', ', $m['topics'])) ?>">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                  <polyline points="2 17 12 22 22 17"></polyline>
                  <polyline points="2 12 12 17 22 12"></polyline>
                </svg>
                <span class="meta-label"><?= count($m['topics']) ?> Topik</span>
              </div>
              <div class="meta-cell">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                  <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
                <span class="meta-label"><?= (int) $m['notes_count'] ?> Catatan</span>
              </div>
            </div>

            <p class="text-sm text-muted" style="margin:0;line-height:1.55;">
              <?= e($m['summary']) ?>
            </p>
          </div>

          <div class="module-footer">
            <a href="sim.php?modul=<?= $no ?>" class="btn btn-outline btn-sm" style="flex:1;">Simulasi</a>
            <a href="quiz.php?modul=<?= $no ?>" class="btn btn-primary btn-sm" style="flex:1.35;">Mulai Kuis</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <?php if (!empty($summary['extra_modules']) || is_admin($user)): ?>
    <section class="mt-6" aria-label="Modul Tambahan">
      <div class="flex items-center justify-between gap-2 mb-4 flex-wrap">
        <div>
          <h2 class="text-lg font-bold" style="margin:0;">Modul Tambahan (Kurikulum Ekstra)</h2>
          <p class="text-xs text-muted" style="margin:0.15rem 0 0;">Materi tambahan yang diunggah dan diproses oleh Admin dari berkas <code class="font-mono">.md</code> / <code class="font-mono">.pdf</code>.</p>
        </div>
        <?php if (is_admin($user)): ?>
          <a href="admin_modul.php" class="btn btn-primary btn-sm">+ Tambah Modul</a>
        <?php endif; ?>
      </div>

      <?php if (empty($summary['extra_modules'])): ?>
        <div class="card card-static p-6 text-sm text-muted">
          Belum ada modul tambahan yang diunggah oleh Admin.
        </div>
      <?php else: ?>
        <div class="grid grid-3 gap-4" style="gap:1.25rem;">
          <?php foreach ($summary['extra_modules'] as $em): ?>
            <article class="card module-card">
              <div class="module-banner module-banner--3">
                <span class="pill-glass"><?= e($em['modul_code']) ?></span>
                <span class="badge"><?= strtoupper(e($em['source_type'])) ?></span>
                <svg class="module-banner-art" width="96" height="96" viewBox="0 0 24 24" fill="none" stroke="#312E81" stroke-width="1.3" aria-hidden="true">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                  <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
              </div>
              <div class="module-body">
                <h3 class="text-lg font-bold" style="margin:0 0 0.25rem;"><?= e($em['judul']) ?></h3>
                <p class="text-xs text-muted" style="margin:0;">Sumber: <code class="font-mono"><?= e($em['original_filename']) ?></code></p>
                <div class="meta-strip" style="grid-template-columns:repeat(2,1fr);">
                  <div class="meta-cell">
                    <span class="meta-label"><?= count($em['topics']) ?> Subtopik</span>
                  </div>
                  <div class="meta-cell">
                    <span class="meta-label"><?= strtoupper(e($em['source_type'])) ?> Terproses</span>
                  </div>
                </div>
                <p class="text-sm text-muted" style="margin:0;line-height:1.55;"><?= e($em['ringkasan']) ?></p>
              </div>
              <div class="module-footer">
                <a href="modul_view.php?id=<?= (int) $em['id'] ?>" class="btn btn-primary btn-sm w-full">Baca Materi</a>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  <?php endif; ?>

  <?php if (!empty($summary['recent_attempts'])): ?>
    <section class="card card-static p-6 mt-6">
      <h2 class="text-lg font-bold mb-4" style="margin-top:0;">Riwayat Percobaan Kuis Terakhir</h2>
      <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:0.875rem;">
          <thead>
            <tr style="border-bottom:2px solid var(--color-border);text-align:left;">
              <th style="padding:0.5rem 0.75rem;">Modul</th>
              <th style="padding:0.5rem 0.75rem;">Skor</th>
              <th style="padding:0.5rem 0.75rem;">Benar</th>
              <th style="padding:0.5rem 0.75rem;">Waktu</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($summary['recent_attempts'] as $att): ?>
              <tr style="border-bottom:1px solid #E2E8F0;">
                <td style="padding:0.5rem 0.75rem;">Modul <?= (int) $att['modul_no'] ?></td>
                <td style="padding:0.5rem 0.75rem;font-weight:600;"><?= e((string) $att['score_percent']) ?>%</td>
                <td style="padding:0.5rem 0.75rem;"><?= (int) $att['correct_count'] ?> / <?= (int) $att['total_questions'] ?></td>
                <td style="padding:0.5rem 0.75rem;" class="text-muted"><?= e($att['created_at']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>
  <?php endif; ?>
</main>
