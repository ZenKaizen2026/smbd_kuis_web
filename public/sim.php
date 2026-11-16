<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/helpers/bootstrap.php';
require_once dirname(__DIR__) . '/app/services/SnapshotResetter.php';
require_once dirname(__DIR__) . '/app/services/SimulatorSandbox.php';

$user = require_auth();
$catalog = require dirname(__DIR__) . '/app/data/modules_catalog.php';

$modulNo = isset($_GET['modul']) ? (int) $_GET['modul'] : 1;
if ($modulNo < 1 || $modulNo > 5) {
    $modulNo = 1;
}

$modulScenarios = SimulatorSandbox::scenariosByModul($modulNo);
$selectedScenarioId = isset($_GET['scenario']) ? (string) $_GET['scenario'] : ($modulScenarios[0]['id'] ?? '');
$validIds = array_column($modulScenarios, 'id');
if (!in_array($selectedScenarioId, $validIds, true)) {
    $selectedScenarioId = $modulScenarios[0]['id'] ?? '';
}

$activeModule = $catalog[0];
foreach ($catalog as $m) {
    if ((int) $m['no'] === $modulNo) {
        $activeModule = $m;
        break;
    }
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['_csrf_token'] ?? null)) {
        $error = 'Sesi formulir tidak valid. Silakan muat ulang halaman.';
    } else {
        $action = (string) ($_POST['action'] ?? '');
        $postModul = (int) ($_POST['modul_no'] ?? $modulNo);
        if ($postModul < 1 || $postModul > 5) {
            $postModul = $modulNo;
        }

        if ($action === 'reset_snapshot') {
            SnapshotResetter::resetLabDatabase();
            set_flash('success', 'Snapshot db_penjualan_lab berhasil di-reset ke kondisi awal (P001 stok 97, LogKasir bersih).');
            redirect_to("sim.php?modul={$postModul}");
        } elseif ($action === 'run_scenario') {
            $scenarioId = (string) ($_POST['scenario_id'] ?? $selectedScenarioId);
            $params = is_array($_POST['params'] ?? null) ? $_POST['params'] : [];
            $result = SimulatorSandbox::run($scenarioId, $params);
            $_SESSION['sim_last_result'] = [
                'scenario_id' => $scenarioId,
                'params' => $params,
                'result' => $result,
            ];
            redirect_to("sim.php?modul={$postModul}&scenario=" . urlencode($scenarioId));
        }
    }
}

$simResult = null;
$lastParams = [];
if (isset($_SESSION['sim_last_result']) && is_array($_SESSION['sim_last_result'])) {
    $saved = $_SESSION['sim_last_result'];
    unset($_SESSION['sim_last_result']);
    if (($saved['scenario_id'] ?? '') === $selectedScenarioId) {
        $simResult = $saved['result'] ?? null;
        $lastParams = is_array($saved['params'] ?? null) ? $saved['params'] : [];
    }
}

$selectedScenario = null;
foreach ($modulScenarios as $s) {
    if ($s['id'] === $selectedScenarioId) {
        $selectedScenario = $s;
        break;
    }
}

$pageTitle = "Simulasi Lab Modul {$modulNo}";
$activeNav = 'sim';
$useAppShell = true;

require dirname(__DIR__) . '/app/views/layouts/header.php';
require dirname(__DIR__) . '/app/views/layouts/navbar.php';
require dirname(__DIR__) . '/app/views/sim/index.php';
require dirname(__DIR__) . '/app/views/layouts/footer.php';
