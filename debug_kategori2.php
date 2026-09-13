<?php
/**
 * Debug Stage 2 — Cari filter yang sebenarnya menghasilkan
 * Safe Action=28%, Unsafe Condition=525 (dashboard) vs 515 (mentor)
 * Total dashboard sekitar 1015
 */
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tahun = 2026;

echo "=== [A] INVESTIGASI FILTER YANG MENGHASILKAN TOTAL ~1015 ===\n\n";

$kategoriList = [
    'Tindakan aman'       => 'Safe Action',
    'Kondisi aman'        => 'Safe Condition',
    'Tindakan tidak aman' => 'Unsafe Action',
    'Kondisi tidak aman'  => 'Unsafe Condition',
];

// Coba berbagai kombinasi filter bulan untuk menemukan mana yang menghasilkan ~1015
echo "--- Filter per-bulan tunggal (Agustus = bulan 8) ---\n";
foreach ([1, 7, 8, 9, 12] as $bln) {
    $blnPad = str_pad($bln, 2, '0', STR_PAD_LEFT);
    $lastDay = date('t', mktime(0, 0, 0, $bln, 1, $tahun));
    $start = "{$tahun}-{$blnPad}-01 00:00";
    $end   = "{$tahun}-{$blnPad}-{$lastDay} 23:59";
    
    $total = 0;
    $breakdown = [];
    foreach (array_keys($kategoriList) as $kat) {
        $c = DB::select("
            SELECT COUNT(*) as cnt FROM sipeka_findings
            WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = ?
              AND STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')), '%Y-%m-%d %H:%i') BETWEEN ? AND ?
        ", [$kat, $start, $end])[0]->cnt;
        $breakdown[$kat] = $c;
        $total += $c;
    }
    
    $uc  = $breakdown['Kondisi tidak aman'];
    $pct = $total > 0 ? round($uc / $total * 100) : 0;
    echo "  Bulan {$bln}: total={$total}, UnsafeCondition={$uc} ({$pct}%)\n";
    foreach ($breakdown as $k => $v) {
        $p = $total > 0 ? round($v/$total*100) : 0;
        echo "    {$k}: {$v} ({$p}%)\n";
    }
    echo "\n";
}

echo "--- Filter YTD Jan-Agustus 2026 ---\n";
$start = "2026-01-01 00:00";
$end   = "2026-08-31 23:59";
$total = 0;
$breakdown = [];
foreach (array_keys($kategoriList) as $kat) {
    $c = DB::select("
        SELECT COUNT(*) as cnt FROM sipeka_findings
        WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = ?
          AND STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')), '%Y-%m-%d %H:%i') BETWEEN ? AND ?
    ", [$kat, $start, $end])[0]->cnt;
    $breakdown[$kat] = $c;
    $total += $c;
}
echo "  YTD Jan-Aug: total={$total}\n";
foreach ($breakdown as $k => $v) {
    $p = $total > 0 ? round($v/$total*100) : 0;
    echo "    {$k}: {$v} ({$p}%)\n";
}
echo "\n";

echo "=== [B] CEK CONTROLLER PARAMETER yang dikirim ke chartKategoriPeka ===\n";
// Lihat apa yang dikirim dari controller ke service
// Buka DashboardController
$controllerPath = app_path('Http/Controllers');
$files = glob($controllerPath . '/*.php');
foreach ($files as $f) {
    echo "  Controller: " . basename($f) . "\n";
}
echo "\n";

echo "=== [C] SIMULASI getCharts() dengan berbagai parameter ===\n";
$service = new App\Services\DashboardChartService();

// Simulasi: null, 2026 (global)
$chartsGlobal = $service->getCharts(null, 2026, null);
echo "  getCharts(null, 2026, null) kategori:\n";
$total = array_sum($chartsGlobal['kategori']);
foreach ($chartsGlobal['kategori'] as $k => $v) {
    $p = $total > 0 ? round($v/$total*100) : 0;
    echo "    {$k}: {$v} ({$p}%)\n";
}
echo "  Total: {$total}\n\n";

// Simulasi: null, 2026, bulan=8
$chartsGlobalBln = $service->getCharts(null, 2026, 8);
echo "  getCharts(null, 2026, 8) kategori:\n";
$total = array_sum($chartsGlobalBln['kategori']);
foreach ($chartsGlobalBln['kategori'] as $k => $v) {
    $p = $total > 0 ? round($v/$total*100) : 0;
    echo "    {$k}: {$v} ({$p}%)\n";
}
echo "  Total: {$total}\n\n";

echo "=== [D] INSPECT REQUEST PARAMETER DARI URL ===\n";
echo "  URL dashboard: ?year=2026&month=8\n";
echo "  Jika bulan=8 tidak digunakan di chartKategoriPeka, total akan 4647\n";
echo "  Jika bulan=8 digunakan, total ~1015\n\n";
echo "  chartKategoriPeka() menerima: (?string \$fungsi, ?int \$tahun)\n";
echo "  PERHATIAN: \$bulan TIDAK diterima oleh chartKategoriPeka!\n";
echo "  baseQuery() hanya filter \$fungsi dan \$tahun\n\n";

echo "=== [E] CARI SUMBER ANGKA 525 dan 515 ===\n";
echo "  Dashboard menampilkan Unsafe Condition = 525\n";
echo "  Ini bukan dari DB query langsung (DB: 2400)\n";
echo "  Kemungkinan: chart JS melakukan kalkulasi berbeda?\n\n";

// Cek nilai per bulan untuk bulan 8
$blnPad = '08';
$start = "2026-{$blnPad}-01 00:00";
$end   = "2026-{$blnPad}-31 23:59";
echo "  Bulan 8 saja:\n";
foreach (array_keys($kategoriList) as $kat) {
    $c = DB::select("
        SELECT COUNT(*) as cnt FROM sipeka_findings
        WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = ?
          AND STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')), '%Y-%m-%d %H:%i') BETWEEN ? AND ?
    ", [$kat, $start, $end])[0]->cnt;
    echo "    {$kat}: {$c}\n";
}
echo "\n";
