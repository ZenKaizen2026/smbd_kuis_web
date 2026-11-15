<?php
declare(strict_types=1);
$activeNav = $activeNav ?? 'dashboard';
$initial = strtoupper(mb_substr($user['nama_lengkap'] ?? 'M', 0, 1));
?>
<div class="app-shell">
  <aside class="sidebar" id="app-sidebar" aria-label="Navigasi Utama">
    <div class="sidebar-header">
      <a href="dashboard.php" class="sidebar-brand" title="SMBDL Lab & Quiz">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#4F46E5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
          <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
          <path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"></path>
        </svg>
        <span class="sidebar-brand-text">SMBDL Lab</span>
      </a>
      <button type="button" class="sidebar-toggle" id="sidebar-toggle-btn" aria-label="Ringkas atau lebarkan navigasi" title="Ringkas / Lebarkan Sidebar">
        <svg class="icon-desktop-sidebar" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <rect x="3" y="3" width="18" height="18" rx="2"></rect>
          <line x1="9" y1="3" x2="9" y2="21"></line>
        </svg>
        <svg class="icon-mobile-burger" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <line x1="3" y1="6" x2="21" y2="6"></line>
          <line x1="3" y1="12" x2="21" y2="12"></line>
          <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
      </button>
    </div>

    <div class="sidebar-menus">
      <div class="sidebar-section-label">Belajar</div>
      <nav class="sidebar-nav">
        <a href="dashboard.php" class="sidebar-item <?= $activeNav === 'dashboard' ? 'active' : '' ?>" title="Dashboard">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="3" y="3" width="7" height="7" rx="1"></rect>
            <rect x="14" y="3" width="7" height="7" rx="1"></rect>
            <rect x="3" y="14" width="7" height="7" rx="1"></rect>
            <rect x="14" y="14" width="7" height="7" rx="1"></rect>
          </svg>
          <span class="sidebar-label">Dashboard</span>
        </a>

        <a href="materi.php" class="sidebar-item <?= $activeNav === 'materi' ? 'active' : '' ?>" title="Materi Tambahan">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
          </svg>
          <span class="sidebar-label">Materi Tambahan</span>
        </a>
      </nav>

      <div class="sidebar-section-label" style="margin-top:0.5rem;">Praktikum</div>
      <nav class="sidebar-nav">
        <a href="quiz.php?modul=1" class="sidebar-item <?= $activeNav === 'quiz' ? 'active' : '' ?>" title="Kuis Modul 1–5">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M9 11l3 3L22 4"></path>
            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
          </svg>
          <span class="sidebar-label">Kuis Modul</span>
        </a>

        <a href="sim.php?modul=1" class="sidebar-item <?= $activeNav === 'sim' ? 'active' : '' ?>" title="Simulasi Sandbox SQL">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polyline points="4 17 10 11 4 5"></polyline>
            <line x1="12" y1="19" x2="20" y2="19"></line>
          </svg>
          <span class="sidebar-label">Simulasi Lab</span>
        </a>
      </nav>

      <?php if (is_admin($user)): ?>
        <div class="sidebar-section-label" style="margin-top:0.5rem;">Admin</div>
        <nav class="sidebar-nav">
          <a href="admin_modul.php" class="sidebar-item <?= $activeNav === 'admin_modul' ? 'active' : '' ?>" title="Tambah Modul (.md / .pdf)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
              <polyline points="17 8 12 3 7 8"></polyline>
              <line x1="12" y1="3" x2="12" y2="15"></line>
            </svg>
            <span class="sidebar-label">Tambah Modul</span>
          </a>
        </nav>
      <?php endif; ?>
    </div>

    <div class="sidebar-footer">
      <div class="flex items-center gap-2" style="min-width:0;">
        <span class="sidebar-avatar" title="<?= e($user['nama_lengkap']) ?>"><?= e($initial) ?></span>
        <div class="sidebar-user-meta" style="min-width:0;overflow:hidden;">
          <div class="text-xs font-semibold" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= e($user['nama_lengkap']) ?></div>
          <div class="text-xs text-muted font-mono" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= e($user['nim_or_username']) ?></div>
        </div>
      </div>

      <form method="POST" action="logout.php" style="margin:0;">
        <?= csrf_field() ?>
        <button type="submit" class="sidebar-toggle" title="Keluar" aria-label="Keluar dari akun">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
            <polyline points="16 17 21 12 16 7"></polyline>
            <line x1="21" y1="12" x2="9" y2="12"></line>
          </svg>
        </button>
      </form>
    </div>
  </aside>

  <div class="main-content">
  <script>
    (function () {
      var sidebar = document.getElementById('app-sidebar');
      var btn = document.getElementById('sidebar-toggle-btn');
      if (!sidebar || !btn) return;
      try {
        if (localStorage.getItem('smbdl_sidebar_collapsed') === '1') {
          sidebar.classList.add('is-collapsed');
        }
      } catch (e) {}
      btn.addEventListener('click', function () {
        sidebar.classList.toggle('is-collapsed');
        try {
          localStorage.setItem('smbdl_sidebar_collapsed', sidebar.classList.contains('is-collapsed') ? '1' : '0');
        } catch (e) {}
      });
    })();
  </script>
