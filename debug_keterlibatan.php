<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\SipekaFinding;
use App\Models\MasterFunctionMapping;
use App\Models\MasterManpower;
use Illuminate\Support\Facades\DB;

$tahun  = 2026;
$bulan  = 5;
$start  = '2026-05-01 00:00';
$end    = '2026-05-31 23:59';
$tc     = "JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal'))";

echo "=== SAMPLE DATA MENTAH OPERATION MEI 2026 ===" . PHP_EOL;
$sipOp = MasterFunctionMapping::getSipekaValues('Operation');
echo "SIPEKA values for Operation: " . implode(', ', $sipOp) . PHP_EOL . PHP_EOL;

$rows = SipekaFinding::query()
    ->whereIn(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"), $sipOp)
    ->whereRaw("STR_TO_DATE({$tc}, '%Y-%m-%d %H:%i') BETWEEN ? AND ?", [$start, $end])
    ->limit(10)
    ->get();

foreach ($rows as $r) {
    $d = $r->data_sipeka;
    echo sprintf(
        "fungsi=%-30s | pelapor=%-30s | tanggal=%s\n",
        $d['fungsi'] ?? '?',
        $d['pelapor'] ?? '?',
        $d['tanggal'] ?? '?'
    );
}

echo PHP_EOL;

// Cek apakah ada field lain yang mungkin nama-nama pelapor
echo "=== STRUKTUR KEY JSON DARI 1 ROW ===" . PHP_EOL;
$one = SipekaFinding::whereRaw("STR_TO_DATE({$tc}, '%Y-%m-%d %H:%i') BETWEEN ? AND ?", [$start, $end])->first();
if ($one) {
    echo "Keys: " . implode(', ', array_keys($one->data_sipeka)) . PHP_EOL;
}

echo PHP_EOL . "=== TOTAL TEMUAN & PELAPOR PER FUNGSI MEI 2026 ===" . PHP_EOL;
$fungsiList = ['Operation', 'Maintenance', 'HSSE', 'Business Support'];
foreach ($fungsiList as $f) {
    $sip = MasterFunctionMapping::getSipekaValues($f);
    if (empty($sip)) {
        echo "  [{$f}]: No SIPEKA mapping found!" . PHP_EOL;
        continue;
    }
    
    $base = SipekaFinding::query()
        ->whereIn(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"), $sip)
        ->whereRaw("STR_TO_DATE({$tc}, '%Y-%m-%d %H:%i') BETWEEN ? AND ?", [$start, $end]);

    $total    = (clone $base)->count();
    $distinct = (clone $base)->distinct()->count(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor'))"));
    $mp       = MasterManpower::getManpower($tahun, $bulan, $f);
    
    $pct = ($mp && $mp > 0) ? round(($distinct / $mp) * 100, 2) . '%' : 'NULL';
    echo "  [{$f}] Total temuan: {$total}, Pelapor unik: {$distinct}, Manpower: " . ($mp ?? 'NULL') . " → {$pct}" . PHP_EOL;
}

echo PHP_EOL . "=== MENTOR REFERENCE ===" . PHP_EOL;
echo "  Operation:        32 pelapor / 62 manpower = 52%" . PHP_EOL;
echo "  Maintenance:      20 pelapor / 54 manpower = 37%" . PHP_EOL;
echo "  HSSE:             22 pelapor / 75 manpower = 29%" . PHP_EOL;
echo "  Business Support: 42 pelapor / 103 manpower = 41%" . PHP_EOL;

echo PHP_EOL . "=== CARI TAHU SEMUA UNIQUE FUNGSI DI DATA MEI ===" . PHP_EOL;
$fungsiDiData = SipekaFinding::query()
    ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi')) as fn, COUNT(*) as cnt")
    ->whereRaw("STR_TO_DATE({$tc}, '%Y-%m-%d %H:%i') BETWEEN ? AND ?", [$start, $end])
    ->groupByRaw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))")
    ->orderByDesc('cnt')
    ->get();

foreach ($fungsiDiData as $row) {
    $mapped = \App\Models\MasterFunctionMapping::where('fungsi_sipeka', $row->fn)->first();
    $mappedTo = $mapped ? ($mapped->fungsi_dashboard ?? 'KelompokKhusus:' . ($mapped->kelompok_khusus ?? '?')) : 'UNMAPPED';
    echo "  '{$row->fn}' → [{$mappedTo}] ({$row->cnt} temuan)" . PHP_EOL;
}
