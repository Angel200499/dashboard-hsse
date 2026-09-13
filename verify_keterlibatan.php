<?php
// Verification: chartKeterlibatan dengan rumus baru
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\DashboardChartService;
use App\Models\MasterManpower;
use App\Models\SipekaFinding;
use Illuminate\Support\Facades\DB;

$tahun = 2026;
$bulan = 5; // Mei

echo "=== VERIFIKASI KETERLIBATAN MEI {$tahun} ===" . PHP_EOL;
echo PHP_EOL;

// Cek manpower yang tersedia
echo "--- Manpower Bulan " . MasterManpower::BULAN_LABELS[$bulan] . " {$tahun} ---" . PHP_EOL;
$fungsiList = ['Operation', 'Maintenance', 'HSSE', 'Business Support'];
foreach ($fungsiList as $f) {
    $mp = MasterManpower::getManpower($tahun, $bulan, $f);
    echo "  {$f}: " . ($mp !== null ? $mp : 'NULL (tidak tersedia)') . PHP_EOL;
}

echo PHP_EOL . "--- Distinct Pelapor Bulan Mei {$tahun} ---" . PHP_EOL;

use App\Models\MasterFunctionMapping;

$bulanPadded = str_pad($bulan, 2, '0', STR_PAD_LEFT);
$lastDay     = date('t', mktime(0, 0, 0, $bulan, 1, $tahun));
$startDate   = "{$tahun}-{$bulanPadded}-01 00:00";
$endDate     = "{$tahun}-{$bulanPadded}-{$lastDay} 23:59";
$tanggalCol  = "JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal'))";

foreach ($fungsiList as $f) {
    $sipValues = MasterFunctionMapping::getSipekaValues($f);
    
    $q = SipekaFinding::query()
        ->whereIn(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"), $sipValues)
        ->whereRaw("STR_TO_DATE({$tanggalCol}, '%Y-%m-%d %H:%i') BETWEEN ? AND ?", [$startDate, $endDate]);
    
    $distinctPelapor = (clone $q)->distinct()->count(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor'))"));
    $totalTemuan     = (clone $q)->count();
    $mp              = MasterManpower::getManpower($tahun, $bulan, $f);
    
    if ($mp !== null && $mp > 0) {
        $rate = round(($distinctPelapor / $mp) * 100, 2);
        echo "  [{$f}] Pelapor unik: {$distinctPelapor}, Temuan: {$totalTemuan}, Manpower: {$mp} → Keterlibatan: {$rate}%" . PHP_EOL;
    } else {
        echo "  [{$f}] Pelapor unik: {$distinctPelapor}, Temuan: {$totalTemuan}, Manpower: NULL → Keterlibatan: NULL (tidak dihitung)" . PHP_EOL;
    }
}

echo PHP_EOL . "--- Via DashboardChartService::getCharts() ---" . PHP_EOL;
$service = new DashboardChartService();
$charts  = $service->getCharts(null, $tahun, $bulan);
echo "keterlibatan: " . PHP_EOL;
foreach ($charts['keterlibatan'] as $f => $v) {
    echo "  {$f}: " . ($v !== null ? $v . '%' : 'NULL') . PHP_EOL;
}

echo PHP_EOL . "--- Test Bulan Tanpa Manpower (Bulan 11 = November) ---" . PHP_EOL;
$charts2 = $service->getCharts(null, $tahun, 11);
echo "keterlibatan: " . PHP_EOL;
foreach ($charts2['keterlibatan'] as $f => $v) {
    echo "  {$f}: " . ($v !== null ? $v . '%' : 'NULL (tidak ada manpower)') . PHP_EOL;
}

echo PHP_EOL . "=== SELESAI ===" . PHP_EOL;
