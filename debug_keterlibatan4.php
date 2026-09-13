<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SipekaFinding;
use App\Models\MasterFunctionMapping;
use Illuminate\Support\Facades\DB;

$tc = "JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal'))";

// Mentor reference
$mentorData = [
    'Operation'        => ['pelapor' => 32, 'manpower' => 62, 'pct' => 52],
    'Maintenance'      => ['pelapor' => 20, 'manpower' => 54, 'pct' => 37],
    'HSSE'             => ['pelapor' => 22, 'manpower' => 75, 'pct' => 29],
    'Business Support' => ['pelapor' => 42, 'manpower' => 103, 'pct' => 41],
];
$fungsiList = array_keys($mentorData);

// =====================================================================
// CEK 1: Mungkin mentor pakai YTD (Jan–Mei) bukan hanya Mei?
// =====================================================================
echo "=== CEK 1: YTD JAN–MEI 2026 (distinct pelapor kumulatif) ===" . PHP_EOL;
$startYTD = '2026-01-01 00:00';
$endMei   = '2026-05-31 23:59';

foreach ($fungsiList as $f) {
    $sip = MasterFunctionMapping::getSipekaValues($f);
    $d = SipekaFinding::query()
        ->whereIn(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"), $sip)
        ->whereRaw("STR_TO_DATE({$tc}, '%Y-%m-%d %H:%i') BETWEEN ? AND ?", [$startYTD, $endMei])
        ->distinct()
        ->count(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor'))"));
    $ref = $mentorData[$f]['pelapor'];
    $mp  = $mentorData[$f]['manpower'];
    $pct = $mp > 0 ? round(($d / $mp) * 100, 2) : '-';
    echo "  [{$f}] YTD pelapor unik: {$d} (mentor: {$ref}) → {$pct}% (mentor: {$mentorData[$f]['pct']}%)" . PHP_EOL;
}

// =====================================================================
// CEK 2: Mungkin "pelapor" di-count sebagai JUMLAH BARIS (bukan unik)?
//         Mentor mungkin pakai total temuan, bukan distinct
// =====================================================================
echo PHP_EOL . "=== CEK 2: JUMLAH TEMUAN (bukan distinct) per bulan Mei ===" . PHP_EOL;
$startMei = '2026-05-01 00:00';
foreach ($fungsiList as $f) {
    $sip = MasterFunctionMapping::getSipekaValues($f);
    $total = SipekaFinding::query()
        ->whereIn(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"), $sip)
        ->whereRaw("STR_TO_DATE({$tc}, '%Y-%m-%d %H:%i') BETWEEN ? AND ?", [$startMei, $endMei])
        ->count();
    $ref = $mentorData[$f]['pelapor'];
    echo "  [{$f}] Total temuan Mei: {$total} (mentor pelapor: {$ref})" . PHP_EOL;
}

// =====================================================================
// CEK 3: Total records di DB — berapa banyak data yang ada?
// =====================================================================
echo PHP_EOL . "=== CEK 3: TOTAL DATA DI DATABASE PER BULAN 2026 ===" . PHP_EOL;
for ($bulan = 1; $bulan <= 9; $bulan++) {
    $bp       = str_pad($bulan, 2, '0', STR_PAD_LEFT);
    $lastDay  = date('t', mktime(0, 0, 0, $bulan, 1, 2026));
    $s = "2026-{$bp}-01 00:00";
    $e = "2026-{$bp}-{$lastDay} 23:59";
    $cnt = SipekaFinding::query()
        ->whereRaw("STR_TO_DATE({$tc}, '%Y-%m-%d %H:%i') BETWEEN ? AND ?", [$s, $e])
        ->count();
    $namabulan = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep'][$bulan-1];
    echo "  {$namabulan} 2026: {$cnt} temuan" . PHP_EOL;
}

// =====================================================================
// CEK 4: Apakah ada field lain selain 'pelapor' yang berisi nama orang?
//         Mungkin mentor pakai 'assign' atau field lain?
// =====================================================================
echo PHP_EOL . "=== CEK 4: SAMPLE SEMUA FIELD YANG MUNGKIN BERISI NAMA PELAPOR ===" . PHP_EOL;
$sample = SipekaFinding::query()
    ->whereRaw("STR_TO_DATE({$tc}, '%Y-%m-%d %H:%i') BETWEEN ? AND ?", [$startMei, $endMei])
    ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi')) = 'Operation LHD'")
    ->first();

if ($sample) {
    $d = $sample->data_sipeka;
    $fieldsOfInterest = ['pelapor', 'assign', 'assigndate', 'closeby', 'verifyby', 'supreme', 'asset_owner'];
    foreach ($fieldsOfInterest as $field) {
        if (isset($d[$field]) && !empty($d[$field])) {
            echo "  {$field}: '{$d[$field]}'" . PHP_EOL;
        }
    }
}

// =====================================================================
// CEK 5: Cek apakah ada data duplikat — mungkin row yang sama di-import 2x
// =====================================================================
echo PHP_EOL . "=== CEK 5: TOTAL UNIQUE idtemuan vs TOTAL BARIS (cek duplikat) ===" . PHP_EOL;
$totalBaris = SipekaFinding::query()
    ->whereRaw("STR_TO_DATE({$tc}, '%Y-%m-%d %H:%i') BETWEEN ? AND ?", [$startMei, $endMei])
    ->count();
$uniqueId = SipekaFinding::query()
    ->whereRaw("STR_TO_DATE({$tc}, '%Y-%m-%d %H:%i') BETWEEN ? AND ?", [$startMei, $endMei])
    ->distinct()
    ->count(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.idtemuan'))"));
echo "  Total baris: {$totalBaris}" . PHP_EOL;
echo "  Unique idtemuan: {$uniqueId}" . PHP_EOL;
echo "  Duplikat: " . ($totalBaris - $uniqueId) . " baris" . PHP_EOL;

// =====================================================================
// CEK 6: KHUSUS OPERATION — tampilkan seluruh pelapor unik per sub-fungsi
//         termasuk berapa yang muncul di YTD
// =====================================================================
echo PHP_EOL . "=== CEK 6: PELAPOR UNIK OPERATION — MEI vs YTD ===" . PHP_EOL;
$sipOp = MasterFunctionMapping::getSipekaValues('Operation');

// YTD
$ytdRows = SipekaFinding::query()
    ->whereIn(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"), $sipOp)
    ->whereRaw("STR_TO_DATE({$tc}, '%Y-%m-%d %H:%i') BETWEEN ? AND ?", [$startYTD, $endMei])
    ->get()
    ->map(fn($r) => $r->data_sipeka['pelapor'] ?? null)
    ->filter()
    ->unique()
    ->values();

// Mei saja
$meiRows = SipekaFinding::query()
    ->whereIn(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"), $sipOp)
    ->whereRaw("STR_TO_DATE({$tc}, '%Y-%m-%d %H:%i') BETWEEN ? AND ?", [$startMei, $endMei])
    ->get()
    ->map(fn($r) => $r->data_sipeka['pelapor'] ?? null)
    ->filter()
    ->unique()
    ->values();

echo "  Mei saja: {$meiRows->count()} pelapor unik" . PHP_EOL;
echo "  YTD Jan–Mei: {$ytdRows->count()} pelapor unik" . PHP_EOL;
echo "  Mentor: 32 pelapor" . PHP_EOL;

echo PHP_EOL . "  Pelapor YTD (Jan–Mei):" . PHP_EOL;
foreach ($ytdRows as $name) {
    $inMei = $meiRows->contains($name) ? '[Mei]' : '[Jan-Apr]';
    echo "    {$inMei} {$name}" . PHP_EOL;
}
