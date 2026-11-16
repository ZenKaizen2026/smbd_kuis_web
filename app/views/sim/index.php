<?php
declare(strict_types=1);

$renderDataTable = static function (array $rows, string $emptyMsg = 'Tidak ada baris data.'): void {
    if (empty($rows)) {
        echo '<p class="text-xs text-muted" style="margin:0;">' . e($emptyMsg) . '</p>';
        return;
    }
    $cols = array_keys($rows[0]);
    echo '<div style="overflow-x:auto;"><table style="width:100%;border-collapse:collapse;font-size:0.8125rem;">';
    echo '<thead><tr style="border-bottom:1px solid var(--color-border);text-align:left;background:#F8FAFC;">';
    foreach ($cols as $col) {
        echo '<th style="padding:0.45rem 0.6rem;white-space:nowrap;">' . e((string) $col) . '</th>';
    }
    echo '</tr></thead><tbody>';
    foreach ($rows as $row) {
        echo '<tr style="border-bottom:1px solid var(--color-border);">';
        foreach ($cols as $col) {
            $val = $row[$col] ?? null;
            echo '<td style="padding:0.45rem 0.6rem;white-space:nowrap;">'
                . ($val === null ? '<span class="text-muted font-mono">NULL</span>' : e((string) $val))
                . '</td>';
        }
        echo '</tr>';
    }
    echo '</tbody></table></div>';
};
?>
<main class="max-w-6xl w-full mx-auto p-6">
  <div class="mb-4 flex items-center justify-between gap-2 flex-wrap">
    <a href="dashboard.php" class="btn btn-outline btn-sm">&larr; Kembali ke Dashboard</a>
    <div class="flex items-center gap-2 flex-wrap">
      <?php foreach ($catalog as $m): ?>
        <?php $mNo = (int) $m['no']; ?>
        <a
          href="sim.php?modul=<?= $mNo ?>"
          class="btn btn-sm <?= $mNo === $modulNo ? 'btn-primary' : 'btn-outline' ?>"
        >
          Modul <?= $mNo ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <section class="card card-static p-6 mb-6">
    <div class="flex items-center justify-between gap-4 flex-wrap">
      <div>
        <span class="badge">Simulator Sandbox SQL · Modul 0<?= $modulNo ?></span>
        <h1 class="text-2xl font-bold mt-2 mb-1"><?= e($activeModule['title']) ?></h1>
        <p class="text-sm text-muted" style="margin:0;">
          Eksekusi skenario aman ter-whitelist pada database terisolasi <code class="font-mono">db_penjualan_lab</code>.
        </p>
      </div>
      <div class="flex items-center gap-2 flex-wrap">
        <a href="quiz.php?modul=<?= $modulNo ?>" class="btn btn-outline btn-sm">Kuis Modul <?= $modulNo ?></a>
        <form method="POST" action="sim.php?modul=<?= $modulNo ?>" style="margin:0;">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="reset_snapshot">
          <input type="hidden" name="modul_no" value="<?= $modulNo ?>">
          <button type="submit" class="btn btn-danger-outline btn-sm">Reset Snapshot Lab</button>
        </form>
      </div>
    </div>
  </section>

  <?php if ($error !== null): ?>
    <div class="alert alert-error mb-4" role="alert"><?= e($error) ?></div>
  <?php endif; ?>

  <div class="grid gap-6" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); align-items: start;">
    <section class="card card-static p-6">
      <h2 class="text-base font-bold mb-3" style="margin-top:0;">Pilih Skenario</h2>
      <div class="flex flex-col gap-2 mb-5">
        <?php foreach ($modulScenarios as $s): ?>
          <?php $isActive = ($s['id'] === $selectedScenarioId); ?>
          <a
            href="sim.php?modul=<?= $modulNo ?>&amp;scenario=<?= urlencode($s['id']) ?>"
            style="display:block;padding:0.75rem;border-radius:0.6rem;border:1px solid <?= $isActive ? '#4F46E5' : 'var(--color-border)' ?>;background:<?= $isActive ? '#EEF2FF' : '#fff' ?>;text-decoration:none;color:inherit;"
          >
            <div class="text-sm font-semibold" style="color:<?= $isActive ? '#312E81' : 'inherit' ?>;"><?= e($s['title']) ?></div>
            <div class="text-xs text-muted mt-1"><?= e($s['description']) ?></div>
          </a>
        <?php endforeach; ?>
      </div>

      <?php if ($selectedScenario !== null): ?>
        <form method="POST" action="sim.php?modul=<?= $modulNo ?>&amp;scenario=<?= urlencode($selectedScenarioId) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="run_scenario">
          <input type="hidden" name="modul_no" value="<?= $modulNo ?>">
          <input type="hidden" name="scenario_id" value="<?= e($selectedScenarioId) ?>">

          <?php if ($selectedScenarioId === 'm1_norm_stepper'): ?>
            <?php $st = (string) ($lastParams['stage'] ?? 'unf'); ?>
            <div class="mb-4">
              <label for="param_stage" class="form-label">Tahap Normalisasi</label>
              <select id="param_stage" name="params[stage]" class="form-input">
                <option value="unf" <?= $st === 'unf' ? 'selected' : '' ?>>UNF (Data Mentah Nota)</option>
                <option value="1nf" <?= $st === '1nf' ? 'selected' : '' ?>>1NF (Atribut Atomik)</option>
                <option value="2nf" <?= $st === '2nf' ? 'selected' : '' ?>>2NF (Bebas Partial Dependency)</option>
                <option value="3nf" <?= $st === '3nf' ? 'selected' : '' ?>>3NF (Skema Final db_penjualan)</option>
              </select>
            </div>
          <?php elseif ($selectedScenarioId === 'm2_constraint_fk'): ?>
            <div class="mb-3">
              <label for="param_nonota" class="form-label">Nonota (Uji FK)</label>
              <input id="param_nonota" type="text" name="params[nonota]" class="form-input font-mono" value="<?= e((string) ($lastParams['nonota'] ?? '9999999999')) ?>" maxlength="10">
            </div>
            <div class="mb-4">
              <label for="param_kd_fk" class="form-label">KodeProduk (Uji FK)</label>
              <input id="param_kd_fk" type="text" name="params[kode_produk]" class="form-input font-mono" value="<?= e((string) ($lastParams['kode_produk'] ?? 'P999')) ?>" maxlength="4">
            </div>
          <?php elseif ($selectedScenarioId === 'm2_constraint_uq'): ?>
            <div class="mb-4">
              <label for="param_nm" class="form-label">NamaProduk (Uji UNIQUE)</label>
              <input id="param_nm" type="text" name="params[nama_produk]" class="form-input" value="<?= e((string) ($lastParams['nama_produk'] ?? 'Indomie goreng special')) ?>" maxlength="100">
            </div>
          <?php elseif ($selectedScenarioId === 'm3_join_inner_left'): ?>
            <?php $jt = (string) ($lastParams['join_type'] ?? 'inner'); ?>
            <div class="mb-4">
              <label for="param_join" class="form-label">Tipe JOIN</label>
              <select id="param_join" name="params[join_type]" class="form-input">
                <option value="inner" <?= $jt === 'inner' ? 'selected' : '' ?>>INNER JOIN</option>
                <option value="left" <?= $jt === 'left' ? 'selected' : '' ?>>LEFT JOIN</option>
              </select>
            </div>
          <?php elseif ($selectedScenarioId === 'm3_view_select'): ?>
            <?php $vn = (string) ($lastParams['view_name'] ?? 'viewdetailtransaksi'); ?>
            <div class="mb-4">
              <label for="param_view" class="form-label">Pilih VIEW</label>
              <select id="param_view" name="params[view_name]" class="form-input font-mono">
                <option value="daftarhargaproduk" <?= $vn === 'daftarhargaproduk' ? 'selected' : '' ?>>daftarhargaproduk</option>
                <option value="tampiltotalnota" <?= $vn === 'tampiltotalnota' ? 'selected' : '' ?>>tampiltotalnota</option>
                <option value="viewdetailtransaksi" <?= $vn === 'viewdetailtransaksi' ? 'selected' : '' ?>>viewdetailtransaksi</option>
                <option value="viewtransaksikasir" <?= $vn === 'viewtransaksikasir' ? 'selected' : '' ?>>viewtransaksikasir</option>
              </select>
            </div>
          <?php elseif ($selectedScenarioId === 'm3_control_flow'): ?>
            <?php $fn = (string) ($lastParams['func'] ?? 'case'); ?>
            <div class="mb-4">
              <label for="param_func" class="form-label">Fungsi Control Flow</label>
              <select id="param_func" name="params[func]" class="form-input font-mono">
                <option value="case" <?= $fn === 'case' ? 'selected' : '' ?>>CASE WHEN ... END</option>
                <option value="if" <?= $fn === 'if' ? 'selected' : '' ?>>IF(kondisi, benar, salah)</option>
                <option value="ifnull" <?= $fn === 'ifnull' ? 'selected' : '' ?>>IFNULL(kolom, fallback)</option>
                <option value="nullif" <?= $fn === 'nullif' ? 'selected' : '' ?>>NULLIF(a, b)</option>
              </select>
            </div>
          <?php elseif ($selectedScenarioId === 'm4_udf_cek_stok'): ?>
            <?php $kp = (string) ($lastParams['kode_produk'] ?? 'P001'); ?>
            <div class="mb-4">
              <label for="param_udf_kd" class="form-label">KodeProduk</label>
              <select id="param_udf_kd" name="params[kode_produk]" class="form-input font-mono">
                <option value="P001" <?= $kp === 'P001' ? 'selected' : '' ?>>P001 - Indomie goreng special</option>
                <option value="P002" <?= $kp === 'P002' ? 'selected' : '' ?>>P002 - Wafer Tango</option>
                <option value="P003" <?= $kp === 'P003' ? 'selected' : '' ?>>P003 - SariRoti Coklat Keju</option>
              </select>
            </div>
          <?php elseif ($selectedScenarioId === 'm5_tg_insert_item'): ?>
            <?php $kp = (string) ($lastParams['kode_produk'] ?? 'P001'); ?>
            <div class="mb-3">
              <label for="param_tg_kd" class="form-label">KodeProduk</label>
              <select id="param_tg_kd" name="params[kode_produk]" class="form-input font-mono">
                <option value="P001" <?= $kp === 'P001' ? 'selected' : '' ?>>P001 - Indomie goreng special</option>
                <option value="P002" <?= $kp === 'P002' ? 'selected' : '' ?>>P002 - Wafer Tango</option>
                <option value="P003" <?= $kp === 'P003' ? 'selected' : '' ?>>P003 - SariRoti Coklat Keju</option>
              </select>
            </div>
            <div class="mb-4">
              <label for="param_qty" class="form-label">Banyak (1–10)</label>
              <input id="param_qty" type="number" name="params[banyak]" class="form-input" min="1" max="10" value="<?= (int) ($lastParams['banyak'] ?? 2) ?>">
            </div>
          <?php elseif ($selectedScenarioId === 'm5_tg_update_kasir'): ?>
            <?php $kk = (string) ($lastParams['kode_kasir'] ?? '001'); ?>
            <div class="mb-3">
              <label for="param_kk" class="form-label">KodeKasir</label>
              <select id="param_kk" name="params[kode_kasir]" class="form-input font-mono">
                <option value="001" <?= $kk === '001' ? 'selected' : '' ?>>001 - Unyil</option>
                <option value="002" <?= $kk === '002' ? 'selected' : '' ?>>002 - Melani</option>
                <option value="003" <?= $kk === '003' ? 'selected' : '' ?>>003 - Doni</option>
                <option value="005" <?= $kk === '005' ? 'selected' : '' ?>>005 - Bambang</option>
              </select>
            </div>
            <div class="mb-4">
              <label for="param_pass" class="form-label">Password Baru (maks. 6 karakter)</label>
              <input id="param_pass" type="text" name="params[pass_baru]" class="form-input font-mono" maxlength="6" value="<?= e((string) ($lastParams['pass_baru'] ?? 'baru01')) ?>">
            </div>
          <?php elseif ($selectedScenarioId === 'm5_tg_delete_item'): ?>
            <?php
              $no = (string) ($lastParams['nonota'] ?? '2208030001');
              $kp = (string) ($lastParams['kode_produk'] ?? 'P001');
            ?>
            <div class="mb-3">
              <label for="param_del_no" class="form-label">Nonota</label>
              <select id="param_del_no" name="params[nonota]" class="form-input font-mono">
                <option value="2208030001" <?= $no === '2208030001' ? 'selected' : '' ?>>2208030001</option>
                <option value="2208030002" <?= $no === '2208030002' ? 'selected' : '' ?>>2208030002</option>
                <option value="2208040001" <?= $no === '2208040001' ? 'selected' : '' ?>>2208040001</option>
                <option value="2208050001" <?= $no === '2208050001' ? 'selected' : '' ?>>2208050001</option>
                <option value="2611130001" <?= $no === '2611130001' ? 'selected' : '' ?>>2611130001</option>
              </select>
            </div>
            <div class="mb-4">
              <label for="param_del_kd" class="form-label">KodeProduk</label>
              <select id="param_del_kd" name="params[kode_produk]" class="form-input font-mono">
                <option value="P001" <?= $kp === 'P001' ? 'selected' : '' ?>>P001 - Indomie goreng special</option>
                <option value="P002" <?= $kp === 'P002' ? 'selected' : '' ?>>P002 - Wafer Tango</option>
                <option value="P003" <?= $kp === 'P003' ? 'selected' : '' ?>>P003 - SariRoti Coklat Keju</option>
              </select>
            </div>
          <?php endif; ?>

          <button type="submit" class="btn btn-primary w-full">Jalankan Simulasi</button>
        </form>
      <?php endif; ?>
    </section>

    <section class="card card-static p-6">
      <h2 class="text-base font-bold mb-3" style="margin-top:0;">Hasil Eksekusi Sandbox</h2>

      <?php if ($simResult === null): ?>
        <p class="text-sm text-muted" style="margin:0;">
          Pilih parameter di sebelah kiri lalu klik <strong>Jalankan Simulasi</strong> untuk mengeksekusi skenario pada <code class="font-mono">db_penjualan_lab</code>.
        </p>
      <?php else: ?>
        <div class="mb-4 flex items-center justify-between gap-2 flex-wrap">
          <span class="badge <?= $simResult['ok'] ? 'badge-paham' : 'badge-ulang' ?>">
            <?= $simResult['ok'] ? 'Eksekusi Berhasil' : 'Error Integritas / Penolakan DBMS' ?>
          </span>
        </div>

        <?php if (!empty($simResult['sql_executed'])): ?>
          <?= render_acrylic_code((string) $simResult['sql_executed'], $selectedScenarioId . '.sql', 'SQL', 'mb-4') ?>
        <?php endif; ?>

        <?php if (!empty($simResult['error'])): ?>
          <div class="alert alert-error mb-4" role="alert">
            <strong>Pesan MySQL:</strong> <code class="font-mono text-xs"><?= e((string) $simResult['error']) ?></code>
          </div>
        <?php endif; ?>

        <?php if (!empty($simResult['explanation'])): ?>
          <div class="text-sm mb-4" style="background:#F8FAFC;border:1px solid var(--color-border);border-radius:0.5rem;padding:0.75rem;">
            <strong>Penjelasan:</strong> <?= e((string) $simResult['explanation']) ?>
          </div>
        <?php endif; ?>

        <?php if (is_array($simResult['trace'] ?? null)): ?>
          <?php $trace = $simResult['trace']; ?>
          <?php if (($trace['kind'] ?? '') === 'normalization'): ?>
            <div class="mb-4">
              <div class="text-xs font-bold uppercase text-muted mb-2"><?= e((string) $trace['label']) ?></div>
              <?php foreach ($trace['tables'] as $tbl): ?>
                <div class="mb-3" style="border:1px solid var(--color-border);border-radius:0.5rem;padding:0.75rem;">
                  <div class="text-xs font-bold mb-2"><?= e((string) $tbl['name']) ?></div>
                  <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:0.8rem;">
                      <thead>
                        <tr style="background:#F8FAFC;border-bottom:1px solid var(--color-border);text-align:left;">
                          <?php foreach ($tbl['headers'] as $h): ?>
                            <th style="padding:0.4rem 0.55rem;white-space:nowrap;"><?= e((string) $h) ?></th>
                          <?php endforeach; ?>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($tbl['rows'] as $r): ?>
                          <tr style="border-bottom:1px solid var(--color-border);">
                            <?php foreach ($r as $cell): ?>
                              <td style="padding:0.4rem 0.55rem;"><?= e((string) $cell) ?></td>
                            <?php endforeach; ?>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php elseif (($trace['kind'] ?? '') === 'venn'): ?>
            <?php $isLeft = (($trace['join_type'] ?? 'inner') === 'left'); ?>
            <div class="mb-4 p-4" style="background:#F8FAFC;border:1px solid var(--color-border);border-radius:0.5rem;text-align:center;">
              <svg width="240" height="120" viewBox="0 0 240 120" role="img" aria-label="Diagram Venn <?= $isLeft ? 'LEFT JOIN' : 'INNER JOIN' ?>">
                <circle cx="90" cy="60" r="46" fill="<?= $isLeft ? 'rgba(79,70,229,0.28)' : 'rgba(148,163,184,0.12)' ?>" stroke="#4F46E5" stroke-width="2" />
                <circle cx="150" cy="60" r="46" fill="rgba(148,163,184,0.12)" stroke="#0EA5E9" stroke-width="2" />
                <path d="M120,25 A46,46 0 0,1 120,95 A46,46 0 0,1 120,25 Z" fill="rgba(79,70,229,0.45)" />
                <text x="68" y="64" font-size="11" font-weight="600" fill="#1E1B4B">Customer</text>
                <text x="150" y="64" font-size="11" font-weight="600" fill="#0369A1">Nota</text>
              </svg>
            </div>
          <?php elseif (($trace['kind'] ?? '') === 'cursor'): ?>
            <div class="mb-4">
              <div class="text-xs font-bold uppercase text-muted mb-2">Trace Iterasi CURSOR (bonusLoop)</div>
              <?php $renderDataTable($trace['rows'] ?? [], 'Tidak ada iterasi.'); ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($simResult['before'])): ?>
          <div class="mb-4">
            <div class="text-xs font-bold uppercase text-muted mb-2">Kondisi Before</div>
            <?php $renderDataTable($simResult['before']); ?>
          </div>
        <?php endif; ?>

        <?php if (!empty($simResult['after'])): ?>
          <div>
            <div class="text-xs font-bold uppercase text-muted mb-2">
              <?= !empty($simResult['before']) ? 'Kondisi After' : 'Hasil Query' ?>
            </div>
            <?php $renderDataTable($simResult['after']); ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </section>
  </div>
</main>
