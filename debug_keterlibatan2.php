<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SipekaFinding;
use App\Models\MasterFunctionMapping;
use Illuminate\Support\Facades\DB;

$start = '2026-05-01 00:00';
$end   = '2026-05-31 23:59';
$tc    = "JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal'))";

echo "=== ANALISIS PELAPOR OPERATION MEI 2026 ===" . PHP_EOL;
echo "(Hanya dari 'Operation LHD' saja)" . PHP_EOL . PHP_EOL;

// Hitung hanya Operation LHD (bukan semua sub-fungsi)
$rowsLHD = SipekaFinding::query()
    ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor')) as pelapor, COUNT(*) as cnt")
    ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi')) = 'Operation LHD'")
    ->whereRaw("STR_TO_DATE({$tc}, '%Y-%m-%d %H:%i') BETWEEN ? AND ?", [$start, $end])
    ->groupByRaw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor'))")
    ->orderByDesc('cnt')
    ->get();

echo "Pelapor unik Operation LHD: " . count($rowsLHD) . PHP_EOL;
foreach ($rowsLHD as $r) {
    echo "  " . $r->pelapor . " ({$r->cnt} temuan)" . PHP_EOL;
}

echo PHP_EOL . "=== ANALISIS PELAPOR SEMUA OPERATION ===" . PHP_EOL;
$sipOp = MasterFunctionMapping::getSipekaValues('Operation');
$rowsAll = SipekaFinding::query()
    ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor')) as pelapor, JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi')) as fn, COUNT(*) as cnt")
    ->whereIn(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"), $sipOp)
    ->whereRaw("STR_TO_DATE({$tc}, '%Y-%m-%d %H:%i') BETWEEN ? AND ?", [$start, $end])
    ->groupByRaw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor')), JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))")
    ->orderByDesc('cnt')
    ->get();

$uniquePelapor = $rowsAll->pluck('pelapor')->unique()->count();
echo "Pelapor unik semua Operation sub-fungsi: {$uniquePelapor}" . PHP_EOL;

// Group by sub-fungsi
$byFungsi = $rowsAll->groupBy('fn');
foreach ($byFungsi as $fn => $items) {
    $uniq = $items->pluck('pelapor')->unique()->count();
    echo "  [{$fn}] Distinct pelapor: {$uniq}" . PHP_EOL;
}

echo PHP_EOL . "=== MENTOR HITUNG BERDASARKAN SIPEKA FUNGSI APA? ===" . PHP_EOL;
echo "Mentor: 32 pelapor Operation, 62 manpower" . PHP_EOL;
echo "Sistem: " . $uniquePelapor . " pelapor Operation (semua sub-fungsi), 68 manpower" . PHP_EOL;

echo PHP_EOL . "=== CEK: APAKAH 'Operation LHD' + 'Operator' = 32 PELAPOR? ===" . PHP_EOL;
$rowsLHDOp = SipekaFinding::query()
    ->whereIn(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"), ['Operation LHD', 'Operator'])
    ->whereRaw("STR_TO_DATE({$tc}, '%Y-%m-%d %H:%i') BETWEEN ? AND ?", [$start, $end])
    ->get();

$uniqLHDOp = $rowsLHDOp->map(fn($r) => $r->data_sipeka['pelapor'] ?? null)->unique()->filter()->count();
echo "Pelapor unik 'Operation LHD' + 'Operator': {$uniqLHDOp}" . PHP_EOL;

echo PHP_EOL . "=== SEMUA FUNGSI — MENTOR vs SISTEM ===" . PHP_EOL;
$fungsiGroups = [
    'Operation'        => ['Operation LHD'],
    'Maintenance'      => ['Maintenance LHD'],
    'HSSE'             => ['HSSE LHD'],
    'Business Support' => ['Business Support LHD'],
];
$mentorManpower = [
    'Operation'        => 62,
    'Maintenance'      => 54,
    'HSSE'             => 75,
    'Business Support' => 103,
];
$mentorPelapor = [
    'Operation'        => 32,
    'Maintenance'      => 20,
    'HSSE'             => 22,
    'Business Support' => 42,
];

foreach ($fungsiGroups as $f => $sipValues) {
    $rows = SipekaFinding::query()
        ->whereIn(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"), $sipValues)
        ->whereRaw("STR_TO_DATE({$tc}, '%Y-%m-%d %H:%i') BETWEEN ? AND ?", [$start, $end])
        ->get();

    $distP = $rows->map(fn($r) => $r->data_sipeka['pelapor'] ?? null)->unique()->filter()->count();
    $mp    = $mentorManpower[$f];
    $pct   = round(($distP / $mp) * 100, 2);

    $mentorP   = $mentorPelapor[$f];
    $mentorPct = round(($mentorP / $mp) * 100, 2);

    echo PHP_EOL . "[{$f}]" . PHP_EOL;
    echo "  Hanya '{$sipValues[0]}' saja:" . PHP_EOL;
    echo "    Pelapor unik: {$distP} | Manpower LHD: {$mp} → {$pct}%" . PHP_EOL;
    echo "  Mentor:" . PHP_EOL;
    echo "    Pelapor unik: {$mentorP} | Manpower LHD: {$mp} → {$mentorPct}%" . PHP_EOL;
}
