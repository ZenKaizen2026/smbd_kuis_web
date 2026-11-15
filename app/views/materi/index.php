<?php
declare(strict_types=1);
?>
<main class="max-w-6xl w-full mx-auto p-6">
  <div class="<?= $isAdmin ? 'grid grid-2 gap-4' : 'flex flex-col gap-4' ?>" style="align-items:start;">
    <?php if ($isAdmin): ?>
      <section class="card card-static p-6">
        <div class="flex items-center justify-between gap-2 mb-1">
          <span class="badge">Admin Knowledge Base</span>
          <span class="badge badge-paham">Akses Admin</span>
        </div>
        <h1 class="text-xl font-bold mt-2 mb-1">Tambah Materi / Knowledge Modul</h1>
        <p class="text-sm text-muted mb-4">
          Materi yang kamu tambahkan di sini akan tampil untuk seluruh mahasiswa sebagai referensi tambahan.
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
            <textarea
              id="contoh_sql"
              name="contoh_sql"
              class="form-input font-mono text-sm"
              rows="3"
              placeholder="SELECT n.Nonota, SUM(i.banyak * i.hargajual) AS 'total nota' FROM Nota n JOIN ItemNota i ON n.Nonota = i.Nonota;"
            ><?= e($old['contoh_sql'] ?? '') ?></textarea>
          </div>

          <button type="submit" class="btn btn-primary w-full">Publikasikan Materi</button>
        </form>
      </section>
    <?php endif; ?>

    <section class="w-full">
      <div class="card card-static p-4 mb-4 flex items-center justify-between gap-2 flex-wrap">
        <div>
          <span class="font-semibold text-sm">Filter Materi Tambahan:</span>
          <?php if (!$isAdmin): ?>
            <span class="text-xs text-muted" style="margin-left:0.5rem;">(Mode Baca Mahasiswa · Dikelola Admin)</span>
          <?php endif; ?>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
          <a href="materi.php" class="nav-link <?= $filterModul === null ? 'active' : '' ?>">Semua</a>
          <?php for ($i = 1; $i <= 5; $i++): ?>
            <a href="materi.php?modul=<?= $i ?>" class="nav-link <?= $filterModul === $i ? 'active' : '' ?>">M<?= $i ?></a>
          <?php endfor; ?>
        </div>
      </div>

      <?php if (empty($notes)): ?>
        <div class="card card-static p-6 text-center text-muted text-sm">
          Belum ada materi tambahan<?= $filterModul ? " untuk Modul {$filterModul}" : '' ?>.
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
                <pre class="sql-panel font-mono mb-4" style="margin-top:0;"><code><?= e($note['contoh_sql']) ?></code></pre>
              <?php endif; ?>

              <?php if ($isAdmin): ?>
                <form method="POST" action="materi.php<?= $filterModul ? '?modul=' . (int) $filterModul : '' ?>" style="margin:0;text-align:right;">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="note_id" value="<?= (int) $note['id'] ?>">
                  <button type="submit" class="btn btn-danger-outline btn-sm">Hapus Materi</button>
                </form>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </div>
</main>
