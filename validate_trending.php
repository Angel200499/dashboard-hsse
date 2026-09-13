<?php
/**
 * Validate chartTrendingTemuan() output
 * Langsung instansiasi service dan panggil method via reflection
 */
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\DashboardChartService;

$service = new DashboardChartService();

// Reflection untuk memanggil private method
$ref = new ReflectionClass($service);
$method = $ref->getMethod('chartTrendingTemuan');
$method->setAccessible(true);

$tahun = 2026;
$result = $method->invoke($service, null, $tahun);

echo "=== VALIDASI chartTrendingTemuan() — Tahun {$tahun} ===\n\n";

echo "Year: " . $result['year'] . "\n";
echo "Annual Total: " . number_format($result['annual_total']) . "\n";
echo "Closed Total: " . number_format($result['closed_total']) . "\n";
echo "Closing Rate: " . $result['closing_rate'] . "%\n\n";

echo "=== MONTHLY BREAKDOWN ===\n";
$sumTotal = 0;
$sumSA = 0; $sumSC = 0; $sumUA = 0; $sumUC = 0;

foreach ($result['months'] as $m) {
    $segmentSum = $m['safe_action'] + $m['safe_condition'] + $m['unsafe_action'] + $m['unsafe_condition'];
    $sumTotal += $m['total'];
    $sumSA += $m['safe_action'];
    $sumSC += $m['safe_condition'];
    $sumUA += $m['unsafe_action'];
    $sumUC += $m['unsafe_condition'];
    
    echo sprintf(
        "  %-4s | SA=%4d SC=%4d UA=%4d UC=%4d | Segment=%4d | Total=%4d%s\n",
        $m['month'],
        $m['safe_action'],
        $m['safe_condition'],
        $m['unsafe_action'],
        $m['unsafe_condition'],
        $segmentSum,
        $m['total'],
        ($m['total'] > $segmentSum ? " [+NA=" . ($m['total'] - $segmentSum) . "]" : "")
    );
}

echo "\n=== VALIDASI KONSISTENSI ===\n";
echo "  Months count:                  " . count($result['months']) . " (expected: 12)\n";
echo "  SUM monthly total:             " . number_format($sumTotal) . " (expected: " . number_format($result['annual_total']) . ")\n";
echo "  Total == Annual Total:         " . ($sumTotal === $result['annual_total'] ? "✓ PASS" : "✗ FAIL") . "\n";
echo "  Closing Rate formula:          " . ($result['annual_total'] > 0 ? round($result['closed_total'] / $result['annual_total'] * 100, 2) : 0) . "% (expected: " . $result['closing_rate'] . "%)\n";
echo "  Annual Total vs DB (6276):     " . ($result['annual_total'] === 6276 ? "✓ PASS" : "✗ FAIL (got " . $result['annual_total'] . ")") . "\n";
echo "  Closed Total vs DB (3871):     " . ($result['closed_total'] === 3871 ? "✓ PASS" : "✗ FAIL (got " . $result['closed_total'] . ")") . "\n";

// Validasi Sep-Des = 0
$sepToDesTotal = 0;
for ($i = 8; $i < count($result['months']); $i++) {
    $sepToDesTotal += $result['months'][$i]['total'];
}
echo "  Sep-Des total (should be 0):   " . ($sepToDesTotal === 0 ? "✓ PASS" : "✗ FAIL (got $sepToDesTotal)") . "\n";

echo "\n=== TOTAL PER KATEGORI (ANNUAL) ===\n";
echo "  Safe Action total:      " . number_format($sumSA) . "\n";
echo "  Safe Condition total:   " . number_format($sumSC) . "\n";
echo "  Unsafe Action total:    " . number_format($sumUA) . "\n";
echo "  Unsafe Condition total: " . number_format($sumUC) . "\n";
$naImplied = $result['annual_total'] - ($sumSA + $sumSC + $sumUA + $sumUC);
echo "  N/A (implied):          " . number_format($naImplied) . " (DB has 1629)\n";
echo "  4-kategori sum:         " . number_format($sumSA + $sumSC + $sumUA + $sumUC) . "\n";

echo "\n=== ALL TESTS DONE ===\n";
