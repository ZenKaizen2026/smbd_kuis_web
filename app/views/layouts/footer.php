<?php
declare(strict_types=1);
if (!empty($useAppShell)): ?>
  </div>
</div>
<?php endif; ?>
<script>
function copyAcrylicCode(btn) {
  var code = btn.getAttribute('data-copy-code') || '';
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(code).then(function () {
      btn.textContent = 'Tersalin';
      setTimeout(function () { btn.textContent = 'Salin'; }, 1600);
    });
  }
}
</script>
</body>
</html>
