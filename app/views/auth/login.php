<?php
declare(strict_types=1);
?>
<main class="min-h-screen flex items-center justify-center p-4">
  <div class="card card-static w-full max-w-md p-8">
    <div class="flex items-center gap-3 mb-4">
      <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
        <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
        <path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"></path>
      </svg>
      <div>
        <span class="badge">SI167 · SMBDL Lab</span>
        <h1 class="text-xl font-bold mt-1" style="margin-bottom:0;">Masuk ke Lab Basis Data</h1>
      </div>
    </div>

    <p class="text-sm text-muted mb-6">
      Latihan kuis &amp; simulasi interaktif Modul 1–5 berbasis <code class="font-mono">db_penjualan</code>.
    </p>

    <?php if (!empty($error)): ?>
      <div class="alert alert-error" role="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php" novalidate>
      <?= csrf_field() ?>
      <div class="mb-4">
        <label for="username" class="form-label">Username atau NIM</label>
        <input
          type="text"
          id="username"
          name="username"
          class="form-input"
          value="<?= e($oldUsername ?? '') ?>"
          placeholder="Contoh: mahasiswa_demo"
          autocomplete="username"
          required
          autofocus
        >
      </div>

      <div class="mb-6">
        <label for="password" class="form-label">Password</label>
        <input
          type="password"
          id="password"
          name="password"
          class="form-input"
          placeholder="Masukkan password"
          autocomplete="current-password"
          required
        >
      </div>

      <button type="submit" class="btn btn-primary w-full">Masuk ke Dashboard</button>
    </form>

    <div class="sql-panel mt-6">
      <div class="text-xs" style="color:#94A3B8;margin-bottom:0.25rem;">-- Akun Demo Seeder</div>
      <code>Username: <strong>mahasiswa_demo</strong> | Pass: <strong>demo1234</strong></code>
    </div>

    <p class="text-sm text-center text-muted mt-6" style="margin-bottom:0;">
      Belum punya akun? <a href="register.php" class="link">Daftar Mahasiswa</a>
    </p>
  </div>
</main>
