<?php
declare(strict_types=1);
?>
<main class="max-w-6xl w-full mx-auto p-6">
  <div class="grid grid-2 gap-4" style="align-items:start;">
    <section class="card card-static p-6">
      <span class="badge">Khusus Admin (<?= e($user['nim_or_username']) ?>)</span>
      <h1 class="text-xl font-bold mt-2 mb-1">Upload &amp; Proses Modul Baru</h1>
      <p class="text-sm text-muted mb-4">
        Unggah berkas <code class="font-mono">.md</code> (disarankan, paling hemat proses) atau <code class="font-mono">.pdf</code> (maks. 5 MB). Sistem akan mengekstrak judul, subtopik, dan ringkasan untuk ditampilkan di Dashboard.
      </p>

      <?php if (!empty($error)): ?>
        <div class="alert alert-error" role="alert"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="admin_modul.php" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="upload">

        <div class="mb-4">
          <label for="modul_code" class="form-label">Kode / Label Modul</label>
          <input
            type="text"
            id="modul_code"
            name="modul_code"
            class="form-input"
            value="<?= e($old['modul_code'] ?? 'Modul 06') ?>"
            placeholder="Contoh: Modul 06"
            required
          >
        </div>

        <div class="mb-4">
          <label for="judul" class="form-label">Judul Modul (Opsional untuk .md)</label>
          <input
            type="text"
            id="judul"
            name="judul"
            class="form-input"
            value="<?= e($old['judul'] ?? '') ?>"
            placeholder="Kosongkan agar otomatis diambil dari # Heading"
          >
        </div>

        <div class="mb-6">
          <label for="modul_file" class="form-label">File Materi (.md atau .pdf, maks. 5 MB)</label>
          <input
            type="file"
            id="modul_file"
            name="modul_file"
            class="form-input"
            accept=".md,.pdf,text/markdown,application/pdf"
            required
          >
        </div>

        <button type="submit" class="btn btn-primary w-full">Proses &amp; Publikasikan ke Dashboard</button>
      </form>
    </section>

    <section>
      <div class="card card-static p-4 mb-4 flex items-center justify-between">
        <span class="font-bold text-base">Daftar Modul Tambahan (<?= count($extraModules) ?>)</span>
        <a href="dashboard.php" class="btn btn-outline btn-sm">Lihat di Dashboard</a>
      </div>

      <?php if (empty($extraModules)): ?>
        <div class="card card-static p-6 text-center text-sm text-muted">
          Belum ada modul tambahan yang diunggah.
        </div>
      <?php else: ?>
        <div class="flex flex-col gap-4">
          <?php foreach ($extraModules as $em): ?>
            <article class="card p-6">
              <div class="flex items-center justify-between gap-2 mb-2">
                <div class="flex items-center gap-2">
                  <span class="badge"><?= e($em['modul_code']) ?></span>
                  <span class="badge"><?= strtoupper(e($em['source_type'])) ?></span>
                </div>
                <span class="text-xs text-muted"><?= e($em['created_at']) ?></span>
              </div>
              <h2 class="text-lg font-bold mt-1 mb-1"><?= e($em['judul']) ?></h2>
              <p class="text-sm text-muted mb-4"><?= e($em['ringkasan']) ?></p>

              <div class="flex items-center justify-between gap-2">
                <a href="modul_view.php?id=<?= (int) $em['id'] ?>" class="btn btn-outline btn-sm">Buka Materi</a>
                <form method="POST" action="admin_modul.php" style="margin:0;">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="module_id" value="<?= (int) $em['id'] ?>">
                  <button type="submit" class="btn btn-danger-outline btn-sm">Hapus Modul</button>
                </form>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>
  </div>
</main>
