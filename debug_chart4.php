<?php
/**
 * Debug: Apa yang dikembalikan chartKeterlibatan() saat:
 *   A) Tahun dipilih, Bulan TIDAK dipilih (null)
 *   B) Tahun dipilih, Bulan dipilih
 *   C) Tidak ada filter sama sekali
 */
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\DashboardChartService;

$service = new DashboardChartService();
$ref = new ReflectionClass($service);
$method = $ref->getMethod('chartKeterlibatan');
$method->setAccessible(true);

echo "=== DEBUG chartKeterlibatan() ===\n\n";

// Skenario A: Tahun = 2026, Bulan = null (yang terjadi saat user hanya pilih tahun)
$resultA = $method->invoke($service, null, 2026, null);
echo "[A] tahun=2026, bulan=null:\n";
foreach ($resultA as $f => $v) {
    echo "  $f => " . var_export($v, true) . "\n";
}
$allNullA = array_reduce($resultA, fn($c, $v) => $c && ($v === null), true);
echo "  allNull? " . ($allNullA ? "YES → chart tidak dirender!" : "NO") . "\n\n";

// Skenario B: Tahun = 2026, Bulan = 8
$resultB = $method->invoke($service, null, 2026, 8);
echo "[B] tahun=2026, bulan=8:\n";
foreach ($resultB as $f => $v) {
    echo "  $f => " . var_export($v, true) . "\n";
}
$allNullB = array_reduce($resultB, fn($c, $v) => $c && ($v === null), true);
echo "  allNull? " . ($allNullB ? "YES → chart tidak dirender!" : "NO") . "\n\n";

// Skenario C: Tidak ada filter
$resultC = $method->invoke($service, null, null, null);
echo "[C] tahun=null, bulan=null:\n";
foreach ($resultC as $f => $v) {
    echo "  $f => " . var_export($v, true) . "\n";
}
$allNullC = array_reduce($resultC, fn($c, $v) => $c && ($v === null), true);
echo "  allNull? " . ($allNullC ? "YES → chart tidak dirender!" : "NO") . "\n\n";

echo "=== ROOT CAUSE ANALYSIS ===\n";
echo "JS logic:\n";
echo "  if (allNull) { tampilkan notice, JANGAN buat chart }\n";
echo "  else { buat chart }\n\n";
echo "MASALAH: Saat user hanya memilih Tahun tanpa Bulan:\n";
echo "  → bulan = null\n";
echo "  → semua fungsi return null\n";
echo "  → allNull = true\n";
echo "  → JS menampilkan notice dan TIDAK membuat canvas chart\n";
echo "  → Canvas kosong — INILAH BUG-nya\n";
