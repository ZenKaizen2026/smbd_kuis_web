# Development Workflow

1. **Modular Step-by-Step**: Kerjakan satu track/step pada satu waktu. Jangan lanjut sebelum diverifikasi dan disetujui user.
2. **TDD & Verification**: Tulis/perbarui test di `tests/run_tests.php`, jalankan `php -l` pada seluruh file PHP, jalankan `php tests/run_tests.php`, dan uji HTTP endpoint lokal via `curl`/Apache.
3. **Git Hygiene**: Branch per step (`feat/step-1-login`, dst.), commit konvensional, tidak meng-commit file di `.gitignore`.
