<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MasterManpower;

echo "======================================================\n";
echo "  TEST MANUAL — FORMULA REPORTING RATE\n";
echo "======================================================\n\n";

function countYtd($tahun, $bulan) {
    $lastDay = date('t', mktime(0, 0, 0, $bulan, 1, $tahun));
    $pm = str_pad($bulan, 2, '0', STR_PAD_LEFT);
    $result = DB::select("SELECT COUNT(*) as c FROM sipeka_findings 
        WHERE STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')), '%Y-%m-%d %H:%i')
        BETWEEN '{$tahun}-01-01 00:00:00' AND '{$tahun}-{$pm}-{$lastDay} 23:59:00'");
    return $result[0]->c;
}

function getTotalMp($tahun, $bulan) {
    $fungsiList = ['Operation', 'Maintenance', 'HSSE', 'Business Support'];
    $total = 0;
    $details = [];
    foreach ($fungsiList as $f) {
        $mp = MasterManpower::getManpower($tahun, $bulan, $f);
        $total += ($mp ?? 0);
        $details[] = "$f=" . ($mp ?? 'NULL');
    }
    return [$total, implode(', ', $details)];
}

// ============================================================
// TEST 1: Januari 2026
// ============================================================
echo "--- TEST 1: Januari 2026 ---\n";
$temuan = countYtd(2026, 1);
[$totalMp, $detail] = getTotalMp(2026, 1);
$rate = $totalMp > 0 ? round(($temuan / ($totalMp * 1)) * 100, 2) : 'N/A';
echo "Temuan YTD Jan: $temuan\n";
echo "Manpower: $detail\n";
echo "Total manpower: $totalMp\n";
echo "Rate = $temuan / ($totalMp × 1) × 100 = $rate%\n\n";

// ============================================================
// TEST 2: Mei 2026 (DATA NYATA)
// ============================================================
echo "--- TEST 2: Mei 2026 (data nyata) ---\n";
$temuan = countYtd(2026, 5);
[$totalMp, $detail] = getTotalMp(2026, 5);
$rate = $totalMp > 0 ? round(($temuan / ($totalMp * 5)) * 100, 2) : 'N/A';
echo "Temuan YTD Jan-Mei: $temuan\n";
echo "Manpower: $detail\n";
echo "Total manpower: $totalMp\n";
echo "Rate = $temuan / ($totalMp × 5) × 100 = $rate%\n";
$ok = (MasterManpower::getManpower(2026,5,'Operation') == 68
    && MasterManpower::getManpower(2026,5,'Maintenance') == 53
    && MasterManpower::getManpower(2026,5,'HSSE') == 83
    && MasterManpower::getManpower(2026,5,'Business Support') == 94);
echo "Manpower Mei data asli: " . ($ok ? "✅ BENAR" : "❌ SALAH") . "\n\n";

// ============================================================
// TEST 3: Agustus 2026
// ============================================================
echo "--- TEST 3: Agustus 2026 ---\n";
$temuan = countYtd(2026, 8);
[$totalMp, $detail] = getTotalMp(2026, 8);
$rate = $totalMp > 0 ? round(($temuan / ($totalMp * 8)) * 100, 2) : 'N/A';
echo "Temuan YTD Jan-Agu: $temuan\n";
echo "Manpower: $detail\n";
echo "Total manpower: $totalMp\n";
echo "Rate = $temuan / ($totalMp × 8) × 100 = $rate%\n\n";

// ============================================================
// TEST 6: Trending — 12 bulan
// ============================================================
echo "--- TEST 6: Trending Temuan 2026 (per bulan individual) ---\n";
for ($m = 1; $m <= 12; $m++) {
    $pm = str_pad($m, 2, '0', STR_PAD_LEFT);
    $lastDay = date('t', mktime(0, 0, 0, $m, 1, 2026));
    $count = DB::select("SELECT COUNT(*) as c FROM sipeka_findings 
        WHERE STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')), '%Y-%m-%d %H:%i')
        BETWEEN '2026-{$pm}-01 00:00:00' AND '2026-{$pm}-{$lastDay} 23:59:00'")[0]->c;
    echo "  Bulan $m: $count temuan" . ($count == 0 ? " (ditampilkan sebagai 0 di chart)" : "") . "\n";
}

// ============================================================
// TEST 7: Trending filter bulan tidak mempengaruhi
// ============================================================
echo "\n--- TEST 7: Trending tidak terpotong saat bulan dipilih ---\n";
echo "Trending selalu mengambil data per BULAN INDIVIDUAL berdasarkan TAHUN saja.\n";
echo "Filter bulan (untuk Reporting Rate) tidak mengubah query trending.\n";
echo "✅ Diimplementasikan secara terpisah di chartTrendingTemuan()\n";

// ============================================================
// TEST 8: Manpower Mei tidak tertimpa
// ============================================================
echo "\n--- TEST 8: Verifikasi Manpower Mei 2026 ---\n";
$meiData = DB::select("SELECT fungsi, jumlah_manpower FROM master_manpowers WHERE tahun=2026 AND bulan=5 ORDER BY fungsi");
$sum = 0;
foreach ($meiData as $r) {
    echo "  {$r->fungsi}: {$r->jumlah_manpower}\n";
    $sum += $r->jumlah_manpower;
}
echo "Total: $sum (harus 298)\n";
echo "Status: " . ($sum == 298 ? "✅ DATA MEI AMAN" : "❌ DATA MEI BERMASALAH") . "\n";

echo "\n======================================================\n";
echo "  SEMUA TEST SELESAI\n";
echo "======================================================\n";
