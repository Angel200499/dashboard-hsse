<?php
/**
 * Debug Stage 5 — REKONSTRUKSI ANGKA 525 DAN 515
 * Cari PERSIS kombinasi yang menghasilkan kedua angka tersebut
 */
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\MasterFunctionMapping;

echo "========================================================\n";
echo "REKONSTRUKSI ANGKA 525 DAN 515\n";
echo "========================================================\n\n";

$tahun = 2026;
$fungsiList = ['Operation', 'Maintenance', 'HSSE', 'Business Support'];
$kategoriMap = [
    'Tindakan aman'       => 'Safe Action',
    'Kondisi aman'        => 'Safe Condition',
    'Tindakan tidak aman' => 'Unsafe Action',
    'Kondisi tidak aman'  => 'Unsafe Condition',
];

// Simulasi service getCharts dengan berbagai parameter
$service = new App\Services\DashboardChartService();

echo "=== SIMULASI getCharts() untuk berbagai parameter ===\n\n";

// Test setiap fungsi
foreach ($fungsiList as $fungsi) {
    $charts = $service->getCharts($fungsi, 2026, null);
    $total = array_sum($charts['kategori']);
    $uc = $charts['kategori']['Unsafe Condition'];
    $pct = $total > 0 ? round($uc/$total*100) : 0;
    echo "  getCharts('{$fungsi}', 2026, null): UC={$uc}/{$total} ({$pct}%)\n";
}
echo "\n";

// Test setiap fungsi dengan bulan
foreach ($fungsiList as $fungsi) {
    for ($bln = 1; $bln <= 8; $bln++) {
        $charts = $service->getCharts($fungsi, 2026, $bln);
        $total = array_sum($charts['kategori']);
        $uc = $charts['kategori']['Unsafe Condition'];
        $pct = $total > 0 ? round($uc/$total*100) : 0;
        if ($uc >= 510 && $uc <= 530) {
            echo "  *** MATCH getCharts('{$fungsi}', 2026, {$bln}): UC={$uc}/{$total} ({$pct}%) ***\n";
        }
    }
}
echo "\n";

// Test global dengan bulan
for ($bln = 1; $bln <= 8; $bln++) {
    $charts = $service->getCharts(null, 2026, $bln);
    $total = array_sum($charts['kategori']);
    $uc = $charts['kategori']['Unsafe Condition'];
    $pct = $total > 0 ? round($uc/$total*100) : 0;
    if ($uc >= 510 && $uc <= 530) {
        echo "  *** MATCH global getCharts(null, 2026, {$bln}): UC={$uc}/{$total} ({$pct}%) ***\n";
    }
}

echo "\n=== SEMUA KOMBINASI YANG MENDEKATI 515-525 ===\n";

// Cek semua fungsi + bulan
foreach (array_merge([null], $fungsiList) as $fungsi) {
    for ($bln = 1; $bln <= 12; $bln++) {
        $charts = $service->getCharts($fungsi, 2026, $bln);
        $total = array_sum($charts['kategori']);
        $uc = $charts['kategori']['Unsafe Condition'];
        $pct = $total > 0 ? round($uc/$total*100) : 0;
        $safeAct = $charts['kategori']['Safe Action'];
        $safeCond = $charts['kategori']['Safe Condition'];
        $unsafeAct = $charts['kategori']['Unsafe Action'];
        
        $saP = $total > 0 ? round($safeAct/$total*100) : 0;
        $scP = $total > 0 ? round($safeCond/$total*100) : 0;
        $uaP = $total > 0 ? round($unsafeAct/$total*100) : 0;
        $ucP = $total > 0 ? round($uc/$total*100) : 0;
        
        // Cek apakah persentase cocok dengan yang dilaporkan (28%, 20%, 1%, 52%)
        if ($saP == 28 && $scP == 20 && $uaP == 1 && $ucP == 51) {
            $fLabel = $fungsi ?? 'global';
            echo "  MATCH PERSENTASE! Fungsi={$fLabel}, Bulan={$bln}: SA={$safeAct}({$saP}%) SC={$safeCond}({$scP}%) UA={$unsafeAct}({$uaP}%) UC={$uc}({$ucP}%) Total={$total}\n";
        }
        if ($saP == 28 && $scP == 20 && $uaP == 1 && $ucP == 52) {
            $fLabel = $fungsi ?? 'global';
            echo "  MATCH PERSENTASE! Fungsi={$fLabel}, Bulan={$bln}: SA={$safeAct}({$saP}%) SC={$safeCond}({$scP}%) UA={$unsafeAct}({$uaP}%) UC={$uc}({$ucP}%) Total={$total}\n";
        }
    }
    // Tanpa filter bulan
    $charts = $service->getCharts($fungsi, 2026, null);
    $total = array_sum($charts['kategori']);
    $uc = $charts['kategori']['Unsafe Condition'];
    $safeAct = $charts['kategori']['Safe Action'];
    $safeCond = $charts['kategori']['Safe Condition'];
    $unsafeAct = $charts['kategori']['Unsafe Action'];
    
    $saP = $total > 0 ? round($safeAct/$total*100) : 0;
    $scP = $total > 0 ? round($safeCond/$total*100) : 0;
    $uaP = $total > 0 ? round($unsafeAct/$total*100) : 0;
    $ucP = $total > 0 ? round($uc/$total*100) : 0;
    
    if ($saP == 28 && $scP == 20 && $uaP == 1 && ($ucP == 51 || $ucP == 52)) {
        $fLabel = $fungsi ?? 'global';
        echo "  MATCH PERSENTASE (no month)! Fungsi={$fLabel}: SA={$safeAct}({$saP}%) SC={$safeCond}({$scP}%) UA={$unsafeAct}({$uaP}%) UC={$uc}({$ucP}%) Total={$total}\n";
    }
}

echo "\n=== KHUSUS: CARI ANGKA EKSAK 525 DAN 515 ===\n";
foreach (array_merge([null], $fungsiList) as $fungsi) {
    for ($bln = 0; $bln <= 12; $bln++) {
        $charts = $bln > 0 
            ? $service->getCharts($fungsi, 2026, $bln)
            : $service->getCharts($fungsi, 2026, null);
        $uc = $charts['kategori']['Unsafe Condition'];
        if ($uc === 525 || $uc === 515) {
            $fLabel = $fungsi ?? 'global';
            $total = array_sum($charts['kategori']);
            $blnLabel = $bln > 0 ? $bln : 'null';
            echo "  *** EXACT MATCH! Fungsi={$fLabel}, Bulan={$blnLabel}: UC={$uc}, Total={$total} ***\n";
        }
    }
}

echo "\n=== CARI ANGKA TANPA FILTER TAHUN ===\n";
foreach (array_merge([null], $fungsiList) as $fungsi) {
    $charts = $service->getCharts($fungsi, null, null);
    $uc = $charts['kategori']['Unsafe Condition'];
    $fLabel = $fungsi ?? 'global';
    $total = array_sum($charts['kategori']);
    echo "  Fungsi={$fLabel}, tahun=null: UC={$uc}, Total={$total}\n";
}

echo "\n=== PERHATIAN: CEK APAKAH getCharts MENGABAIKAN BULAN DI KATEGORI ===\n";
// Dari kode: chartKategoriPeka() hanya menerima ($fungsi, $tahun)
// Bulan tidak digunakan! Jadi getCharts(null, 2026, 8) == getCharts(null, 2026, null)
$charts1 = $service->getCharts(null, 2026, null);
$charts2 = $service->getCharts(null, 2026, 8);
echo "  getCharts(null, 2026, null) UC: " . $charts1['kategori']['Unsafe Condition'] . "\n";
echo "  getCharts(null, 2026, 8)    UC: " . $charts2['kategori']['Unsafe Condition'] . "\n";
echo "  Sama? " . ($charts1['kategori']['Unsafe Condition'] === $charts2['kategori']['Unsafe Condition'] ? 'YA' : 'TIDAK') . "\n\n";
