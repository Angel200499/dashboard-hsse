<?php
/**
 * Inspect Keterlibatan data untuk memahami gap antara implementasi existing
 * dan definisi mentor (pelapor unik vs jumlah laporan).
 */
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\MasterFunctionMapping;
use App\Models\MasterManpower;
use App\Models\SipekaFinding;

$tahun = 2026;
$bulan = 8; // Agustus — bulan terakhir yang ada data

echo "=== INSPECT KETERLIBATAN DALAM OBSERVASI ===\n";
echo "Tahun: $tahun | Bulan filter: $bulan\n\n";

$fungsiList = ['Operation', 'Maintenance', 'HSSE', 'Business Support'];
$tanggalCol = "JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal'))";

// -------------------------------------------------------
// SKENARIO A — Existing: filter bulan spesifik (monthQuery)
// -------------------------------------------------------
echo "=== [A] EXISTING: Pelapor unik BULAN SPESIFIK ($bulan/$tahun) ===\n";
$bulanPadded = str_pad($bulan, 2, '0', STR_PAD_LEFT);
$lastDay = date('t', mktime(0, 0, 0, $bulan, 1, $tahun));
$startDate = "{$tahun}-{$bulanPadded}-01 00:00";
$endDate = "{$tahun}-{$bulanPadded}-{$lastDay} 23:59";

foreach ($fungsiList as $f) {
    $sipValues = MasterFunctionMapping::getSipekaValues($f);
    $q = SipekaFinding::query();
    if (!empty($sipValues)) {
        $q->whereIn(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"), $sipValues);
    } else {
        $q->where('data_sipeka->fungsi', 'like', "%{$f}%");
    }
    $q->whereRaw("STR_TO_DATE({$tanggalCol}, '%Y-%m-%d %H:%i') BETWEEN ? AND ?", [$startDate, $endDate]);

    $distinctPelapor = $q->distinct()->count(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor'))"));
    $totalLaporan    = $q->count();
    $manpower        = MasterManpower::getManpower($tahun, $bulan, $f);

    $rate = ($manpower && $manpower > 0) ? round($distinctPelapor / $manpower * 100, 2) : null;
    echo "  $f:\n";
    echo "    Distinct pelapor (bulan $bulan): $distinctPelapor\n";
    echo "    Total laporan (bulan $bulan): $totalLaporan\n";
    echo "    Manpower: " . ($manpower ?? 'N/A') . "\n";
    echo "    Rate: " . ($rate !== null ? $rate . "%" : "N/A (manpower belum ada)") . "\n";
}

// -------------------------------------------------------
// SKENARIO B — YTD: Pelapor unik Jan s/d bulan
// -------------------------------------------------------
echo "\n=== [B] YTD: Pelapor unik Januari s/d Bulan $bulan (Tahun $tahun) ===\n";
$startYtd = "{$tahun}-01-01 00:00";
$endYtd   = "{$tahun}-{$bulanPadded}-{$lastDay} 23:59";

foreach ($fungsiList as $f) {
    $sipValues = MasterFunctionMapping::getSipekaValues($f);
    $q = SipekaFinding::query();
    if (!empty($sipValues)) {
        $q->whereIn(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"), $sipValues);
    } else {
        $q->where('data_sipeka->fungsi', 'like', "%{$f}%");
    }
    $q->whereRaw("STR_TO_DATE({$tanggalCol}, '%Y-%m-%d %H:%i') BETWEEN ? AND ?", [$startYtd, $endYtd]);

    $distinctPelapor = $q->distinct()->count(DB::raw("NULLIF(TRIM(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor'))), '')"));
    $totalLaporan    = $q->count();
    $manpower        = MasterManpower::getManpower($tahun, $bulan, $f);

    $rate = ($manpower && $manpower > 0) ? round($distinctPelapor / $manpower * 100, 2) : null;
    echo "  $f:\n";
    echo "    Distinct pelapor unik (YTD Jan-$bulan): $distinctPelapor\n";
    echo "    Total laporan (YTD): $totalLaporan\n";
    echo "    Manpower: " . ($manpower ?? 'N/A') . "\n";
    echo "    Rate: " . ($rate !== null ? $rate . "%" : "N/A (manpower belum ada)") . "\n";
}

// -------------------------------------------------------
// SKENARIO C — FULL YEAR: Pelapor unik seluruh tahun
// -------------------------------------------------------
echo "\n=== [C] FULL YEAR: Pelapor unik seluruh tahun $tahun ===\n";
foreach ($fungsiList as $f) {
    $sipValues = MasterFunctionMapping::getSipekaValues($f);
    $q = SipekaFinding::query();
    if (!empty($sipValues)) {
        $q->whereIn(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"), $sipValues);
    } else {
        $q->where('data_sipeka->fungsi', 'like', "%{$f}%");
    }
    $q->whereRaw("{$tanggalCol} LIKE ?", ["%{$tahun}%"]);

    $distinctPelapor = $q->distinct()->count(DB::raw("NULLIF(TRIM(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor'))), '')"));
    $totalLaporan    = $q->count();
    
    // Semua manpower bulan tersedia?
    $allManpower = [];
    for ($b = 1; $b <= 8; $b++) {
        $mp = MasterManpower::getManpower($tahun, $b, $f);
        if ($mp !== null) $allManpower[$b] = $mp;
    }
    
    echo "  $f:\n";
    echo "    Distinct pelapor (full year): $distinctPelapor\n";
    echo "    Total laporan (full year): $totalLaporan\n";
    echo "    Manpower per bulan: " . json_encode($allManpower) . "\n";
}

// -------------------------------------------------------
// SAMPLE PELAPOR AKTUAL PER FUNGSI
// -------------------------------------------------------
echo "\n=== [D] SAMPLE PELAPOR AKTUAL (bulan $bulan, Operation) ===\n";
$sipValuesOp = MasterFunctionMapping::getSipekaValues('Operation');
$qSample = SipekaFinding::query();
if (!empty($sipValuesOp)) {
    $qSample->whereIn(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"), $sipValuesOp);
}
$qSample->whereRaw("STR_TO_DATE({$tanggalCol}, '%Y-%m-%d %H:%i') BETWEEN ? AND ?", [$startDate, $endDate]);
$samples = $qSample->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor')) as pelapor, COUNT(*) as cnt")
    ->groupBy('pelapor')
    ->orderByDesc('cnt')
    ->limit(10)
    ->get();
foreach ($samples as $s) {
    echo "    pelapor='" . ($s->pelapor ?: '(kosong)') . "' laporan=" . $s->cnt . "\n";
}

// -------------------------------------------------------
// MANPOWER TERSEDIA
// -------------------------------------------------------
echo "\n=== [E] DATA MANPOWER TERSEDIA ===\n";
$mpRows = DB::table('master_manpowers')
    ->where('tahun', $tahun)
    ->orderBy('fungsi')->orderBy('bulan')
    ->get(['fungsi', 'bulan', 'jumlah_manpower']);
foreach ($mpRows as $r) {
    echo "  {$r->fungsi} | Bulan {$r->bulan} | {$r->jumlah_manpower}\n";
}
if ($mpRows->isEmpty()) {
    echo "  Tidak ada data manpower untuk tahun $tahun\n";
}
