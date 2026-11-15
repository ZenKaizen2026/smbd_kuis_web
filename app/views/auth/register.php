<?php
declare(strict_types=1);
?>
<main class="min-h-screen flex items-center justify-center p-4">
  <div class="card card-static w-full max-w-md p-8">
    <div class="mb-4">
      <span class="badge">SI167 · Registrasi</span>
      <h1 class="text-xl font-bold mt-2" style="margin-bottom:0;">Daftar Akun Mahasiswa</h1>
    </div>

    <p class="text-sm text-muted mb-6">
      Progres Modul 1–5 dan riwayat skor kuismu akan tersimpan secara lokal di <code class="font-mono">db_smbd_kuis</code>.
    </p>

    <?php if (!empty($error)): ?>
      <div class="alert alert-error" role="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="register.php" novalidate>
      <?= csrf_field() ?>
      <div class="mb-4">
        <label for="username" class="form-label">Username atau NIM</label>
        <input
          type="text"
          id="username"
          name="username"
          class="form-input"
          value="<?= e($old['username'] ?? '') ?>"
          placeholder="Contoh: 25.12.3442"
          autocomplete="username"
          required
          autofocus
        >
      </div>

      <div class="mb-4">
        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
        <input
          type="text"
          id="nama_lengkap"
          name="nama_lengkap"
          class="form-input"
          value="<?= e($old['nama_lengkap'] ?? '') ?>"
          placeholder="Nama mahasiswa"
          autocomplete="name"
          required
        >
      </div>

      <div class="mb-4">
        <label for="password" class="form-label">Password (min. 6 karakter)</label>
        <input
          type="password"
          id="password"
          name="password"
          class="form-input"
          placeholder="Minimal 6 karakter"
          autocomplete="new-password"
          required
        >
      </div>

      <div class="mb-6">
        <label for="password_confirm" class="form-label">Konfirmasi Password</label>
        <input
          type="password"
          id="password_confirm"
          name="password_confirm"
          class="form-input"
          placeholder="Ulangi password"
          autocomplete="new-password"
          required
        >
      </div>

      <button type="submit" class="btn btn-primary w-full">Buat Akun</button>
    </form>

    <p class="text-sm text-center text-muted mt-6" style="margin-bottom:0;">
      Sudah punya akun? <a href="login.php" class="link">Kembali ke Login</a>
    </p>
  </div>
</main>
