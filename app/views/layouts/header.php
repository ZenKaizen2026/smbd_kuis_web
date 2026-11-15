<?php
declare(strict_types=1);
$pageTitle = $pageTitle ?? 'SMBDL Lab & Quiz';
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?> — SMBDL SI167</title>
  <link rel="stylesheet" href="assets/css/tailwind.min.css">
</head>
<body class="min-h-screen flex flex-col">
<?php if ($flash !== null): ?>
  <div class="toast-container" id="toast-container">
    <div class="toast-alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-error' ?>" role="alert" id="flash-toast">
      <span><?= e($flash['message']) ?></span>
      <button type="button" class="toast-close" aria-label="Tutup notifikasi" onclick="dismissFlashToast()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
      </button>
    </div>
  </div>
  <script>
    function dismissFlashToast() {
      var el = document.getElementById('flash-toast');
      if (!el || el.classList.contains('is-leaving')) return;
      el.classList.add('is-leaving');
      setTimeout(function () {
        var wrap = document.getElementById('toast-container');
        if (wrap) wrap.remove();
      }, 340);
    }
    setTimeout(dismissFlashToast, 3500);
  </script>
<?php endif; ?>
