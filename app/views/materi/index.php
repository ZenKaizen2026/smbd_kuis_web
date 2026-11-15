<?php
declare(strict_types=1);
?>
<main class="max-w-6xl w-full mx-auto p-6">
  <div class="grid grid-2 gap-4" style="align-items:start;">
    <section class="card card-static p-6">
      <span class="badge">Knowledge Tambahan</span>
      <h1 class="text-xl font-bold mt-2 mb-1">Tambah Materi / Catatan Modul</h1>
      <p class="text-sm text-muted mb-4">
        Simpan ringkasan, sintaks SQL penting, atau catatan praktikum tambahan untuk memperkaya referensi belajarmu.
      </p>

      <?php if (!empty($error)): ?>
        <div class="alert alert-error" role="alert"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="materi.php<?= $filterModul ? '?modul=' . (int) $filterModul : '' ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">

        <div class="mb-4">
          <label for="modul_no" class="form-label">Pilih Modul Terkait</label>
          <select id="modul_no" name="modul_no" class="form-input" required>
            <?php foreach ($catalog as $no => $meta): ?>
              <option value="<?= (int) $no ?>" <?= ($old['modul_no'] ?? $filterModul ?? 1) == $no ? 'selected' : '' ?>>
                Modul <?= (int) $no ?> — <?= e($meta['title']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-4">
          <label for="judul" class="form-label">Judul Materi / Topik</label>
          <input
            type="text"
            id="judul"
            name="judul"
            class="form-input"
            value="<?= e($old['judul'] ?? '') ?>"
            placeholder="Contoh: Beda Table Alias t1/n dan Header AS"
            required
          >
        </div>

        <div class="mb-4">
          <label for="isi_materi" class="form-label">Penjelasan / Ringkasan</label>
          <textarea
            id="isi_materi"
            name="isi_materi"
            class="form-input"
            rows="4"
            placeholder="Tulis pemahaman atau poin penting dari modul/laporan..."
            required
          ><?= e($old['isi_materi'] ?? '') ?></textarea>
        </div>

        <div class="mb-6">
          <label for="contoh_sql" class="form-label">Contoh Sintaks SQL (Opsional)</label>
          <div class="acrylic-code-card">
            <div class="acrylic-code-header">
              <div class="acrylic-code-tabs">
                <span class="acrylic-code-tab active">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#38BDF8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                    <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                    <path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"></path>
                  </svg>
                  <span>catatan_query.sql</span>
                </span>
              </div>
              <div class="acrylic-code-actions">
                <span class="acrylic-code-lang">SQL</span>
              </div>
            </div>
            <textarea
              id="contoh_sql"
              name="contoh_sql"
              class="acrylic-code-textarea font-mono"
              rows="4"
              placeholder="SELECT n.Nonota, SUM(i.banyak * i.hargajual) AS 'total nota' FROM Nota n JOIN ItemNota i ON n.Nonota = i.Nonota;"
            ><?= e($old['contoh_sql'] ?? '') ?></textarea>
          </div>
        </div>

        <button type="submit" class="btn btn-primary w-full">Simpan Materi Tambahan</button>
      </form>
    </section>

    <section>
      <div class="card card-static p-4 mb-4 flex items-center justify-between gap-2 flex-wrap">
        <span class="font-semibold text-sm">Filter Modul:</span>
        <div class="flex items-center gap-2 flex-wrap">
          <a href="materi.php" class="nav-link <?= $filterModul === null ? 'active' : '' ?>">Semua</a>
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <a href="materi.php?modul=<?= $i ?>" class="nav-link <?= $filterModul === $i ? 'active' : '' ?>">M<?= $i ?></a>
          <?php endfor; ?>
        </div>
      </div>

      <?php if (empty($notes)): ?>
        <div class="card card-static p-6 text-center text-muted text-sm">
          Belum ada catatan materi tambahan<?= $filterModul ? " untuk Modul {$filterModul}" : '' ?>. Tambahkan melalui formulir di samping!
        </div>
      <?php else: ?>
        <div class="flex flex-col gap-4">
          <?php foreach ($notes as $note): ?>
            <article class="card p-6">
              <div class="flex items-center justify-between gap-2 mb-2">
                <span class="badge">Modul <?= (int) $note['modul_no'] ?></span>
                <span class="text-xs text-muted"><?= e($note['created_at']) ?></span>
              </div>
              <h2 class="text-lg font-bold mt-1 mb-2"><?= e($note['judul']) ?></h2>
              <p class="text-sm text-muted mb-4" style="white-space:pre-line;"><?= e($note['isi_materi']) ?></p>

              <?php if (!empty($note['contoh_sql'])): ?>
                <?= render_acrylic_code((string) $note['contoh_sql'], 'modul_' . (int) $note['modul_no'] . '_catatan.sql', 'SQL', 'mb-4') ?>
              <?php endif; ?>

              <form method="POST" action="materi.php<?= $filterModul ? '?modul=' . (int) $filterModul : '' ?>" style="margin:0;text-align:right;">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="note_id" value="<?= (int) $note['id'] ?>">
                <button type="submit" class="btn btn-danger-outline btn-sm">Hapus Catatan</button>
              </form>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </div>
</main>
