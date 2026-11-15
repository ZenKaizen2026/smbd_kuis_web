<?php
declare(strict_types=1);
?>
<main class="max-w-4xl w-full mx-auto p-6">
  <div class="mb-4 flex items-center justify-between gap-2 flex-wrap">
    <a href="dashboard.php" class="btn btn-outline btn-sm">&larr; Kembali ke Dashboard</a>
    <div class="flex items-center gap-2">
      <span class="badge"><?= e($module['modul_code']) ?></span>
      <span class="badge"><?= strtoupper(e($module['source_type'])) ?></span>
    </div>
  </div>

  <article class="card card-static p-8">
    <h1 class="text-2xl font-bold mb-1" style="margin-top:0;"><?= e($module['judul']) ?></h1>
    <p class="text-xs text-muted mb-6">
      Berkas sumber: <code class="font-mono"><?= e($module['original_filename']) ?></code> · Diunggah <?= e($module['created_at']) ?>
    </p>

    <div class="modul-content">
      <?= $module['konten_html'] ?>
    </div>

    <?php if ($module['source_type'] === 'pdf'): ?>
      <div class="mt-6" style="border-top:1px solid var(--color-border);padding-top:1.25rem;">
        <div class="flex items-center justify-between gap-2 mb-3 flex-wrap">
          <h2 class="text-base font-bold" style="margin:0;">Dokumen PDF Asli</h2>
          <a href="modul_file.php?id=<?= (int) $module['id'] ?>" target="_blank" rel="noopener" class="btn btn-outline btn-sm">Buka PDF di Tab Baru</a>
        </div>
        <iframe
          src="modul_file.php?id=<?= (int) $module['id'] ?>"
          title="<?= e($module['judul']) ?>"
          style="width:100%;height:520px;border:1px solid var(--color-border);border-radius:0.75rem;"
        ></iframe>
      </div>
    <?php endif; ?>
  </article>
</main>
