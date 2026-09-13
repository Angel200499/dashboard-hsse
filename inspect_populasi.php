<?php
/**
 * Inspect: bagaimana menentukan "total populasi" per fungsi
 * dari data SIPEKA yang ada — tanpa menggunakan MasterManpower.
 */
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\MasterFunctionMapping;
use App\Models\SipekaFinding;

$tahun = 2026;
$tanggalCol = "JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal'))";
$fungsiList = ['Operation', 'Maintenance', 'HSSE', 'Business Support'];

echo "=== ANALISIS POPULASI FUNGSI — TAHUN $tahun ===\n\n";

// -------------------------------------------------------
// Hitung pelapor unik per fungsi (seluruh tahun) 
// Dan total populasi = seluruh pelapor unik semua tahun (all-time)
// -------------------------------------------------------
foreach ($fungsiList as $f) {
    $sipValues = MasterFunctionMapping::getSipekaValues($f);
    
    // Pelapor unik tahun ini
    $qYear = SipekaFinding::query();
    if (!empty($sipValues)) {
        $qYear->whereIn(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"), $sipValues);
    } else {
        $qYear->where('data_sipeka->fungsi', 'like', "%{$f}%");
    }
    $qYear->whereRaw("{$tanggalCol} LIKE ?", ["%{$tahun}%"]);
    $pelapor_tahun = $qYear->distinct()
        ->count(DB::raw("NULLIF(TRIM(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor'))), '')"));
    
    // Semua pelapor unik all-time (populasi keseluruhan)
    $qAll = SipekaFinding::query();
    if (!empty($sipValues)) {
        $qAll->whereIn(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"), $sipValues);
    } else {
        $qAll->where('data_sipeka->fungsi', 'like', "%{$f}%");
    }
    $pelapor_all_time = $qAll->distinct()
        ->count(DB::raw("NULLIF(TRIM(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor'))), '')"));
    
    // Jika "total populasi" = seluruh pelapor all-time
    $rate_vs_alltime = $pelapor_all_time > 0
        ? round($pelapor_tahun / $pelapor_all_time * 100, 2) : 0;
    // Jika "total populasi" = pelapor unik tahun itu sendiri → selalu 100%
    $rate_self = 100; // tautologis

    echo "  $f:\n";
    echo "    Pelapor unik tahun $tahun:   $pelapor_tahun\n";
    echo "    Pelapor unik all-time:       $pelapor_all_time\n";
    echo "    Rate (vs all-time):          {$rate_vs_alltime}%\n";
    echo "    [NOTE: pelapor_tahun / pelapor_tahun = 100% — tautologis]\n\n";
}

// -------------------------------------------------------
// Skenario: populasi = MasterManpower (existing, seperti PR sebelumnya)
// -------------------------------------------------------
echo "\n=== SKENARIO MANPOWER (existing, bulan=8) ===\n";
use App\Models\MasterManpower;
$bulan = 8;
foreach ($fungsiList as $f) {
    $sipValues = MasterFunctionMapping::getSipekaValues($f);
    
    $q = SipekaFinding::query();
    if (!empty($sipValues)) {
        $q->whereIn(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"), $sipValues);
    } else {
        $q->where('data_sipeka->fungsi', 'like', "%{$f}%");
    }
    $q->whereRaw("{$tanggalCol} LIKE ?", ["%{$tahun}%"]);
    $pelapor_tahun = $q->distinct()
        ->count(DB::raw("NULLIF(TRIM(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor'))), '')"));
    
    $mp = MasterManpower::getManpower($tahun, $bulan, $f);
    $rate = ($mp && $mp > 0) ? round($pelapor_tahun / $mp * 100, 2) : null;
    
    echo "  $f: pelapor_unik=$pelapor_tahun / manpower[$bulan]=" . ($mp ?? 'N/A') . " = " . ($rate !== null ? $rate . "%" : "N/A") . "\n";
}

// -------------------------------------------------------
// Sample nama pelapor per fungsi — apakah ada null/kosong?
// -------------------------------------------------------
echo "\n=== SAMPLE NILAI PELAPOR — APAKAH ADA NULL/KOSONG? ===\n";
foreach ($fungsiList as $f) {
    $sipValues = MasterFunctionMapping::getSipekaValues($f);
    $q = SipekaFinding::query();
    if (!empty($sipValues)) {
        $q->whereIn(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"), $sipValues);
    } else {
        $q->where('data_sipeka->fungsi', 'like', "%{$f}%");
    }
    $q->whereRaw("{$tanggalCol} LIKE ?", ["%{$tahun}%"]);
    
    $nullOrEmpty = $q->whereRaw("TRIM(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor'))) = '' OR JSON_EXTRACT(data_sipeka, '$.pelapor') IS NULL")->count();
    $total = $q->count();
    echo "  $f: total=$total | null/kosong=$nullOrEmpty\n";
}

echo "\n=== KESIMPULAN ANALISIS ===\n";
echo "Jika 'total populasi' = pelapor unik all-time → tidak tautologis tapi tidak representatif per tahun\n";
echo "Jika 'total populasi' = pelapor unik tahun itu → selalu 100% (tautologis)\n";
echo "Jika 'total populasi' = MasterManpower (jumlah karyawan) → metric bermakna\n";
echo "REKOMENDASI: Gunakan MasterManpower sebagai denominator (populasi karyawan resmi)\n";
