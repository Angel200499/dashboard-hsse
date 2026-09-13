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

// =====================================================================
// STEP 1: Cek mapping Operation
// =====================================================================
echo "=== STEP 1: MAPPING OPERATION ===" . PHP_EOL;
$mappings = MasterFunctionMapping::where('fungsi_dashboard', 'Operation')->get();
foreach ($mappings as $m) {
    echo "  '{$m->fungsi_sipeka}' → {$m->fungsi_dashboard}" . PHP_EOL;
}
$sipOp = MasterFunctionMapping::getSipekaValues('Operation');
echo PHP_EOL . "getSipekaValues('Operation') returns " . count($sipOp) . " values:" . PHP_EOL;
foreach ($sipOp as $v) {
    echo "  - '{$v}'" . PHP_EOL;
}

// =====================================================================
// STEP 2: Semua temuan Mei 2026 per fungsi SIPEKA
// =====================================================================
echo PHP_EOL . "=== STEP 2: SEMUA TEMUAN MEI 2026 PER FUNGSI SIPEKA ===" . PHP_EOL;
$allMei = SipekaFinding::query()
    ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi')) as fn, COUNT(*) as cnt")
    ->whereRaw("STR_TO_DATE({$tc}, '%Y-%m-%d %H:%i') BETWEEN ? AND ?", [$start, $end])
    ->groupByRaw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))")
    ->orderByDesc('cnt')
    ->get();

$totalMei = 0;
foreach ($allMei as $row) {
    $mapped = MasterFunctionMapping::where('fungsi_sipeka', $row->fn)->first();
    $dest   = $mapped ? ($mapped->fungsi_dashboard ?? 'KelompokKhusus:' . ($mapped->kelompok_khusus ?? '?')) : 'UNMAPPED';
    echo "  '{$row->fn}' → [{$dest}] ({$row->cnt} temuan)" . PHP_EOL;
    $totalMei += $row->cnt;
}
echo "  TOTAL: {$totalMei} temuan" . PHP_EOL;

// =====================================================================
// STEP 3: Data mentah pelapor Operation Mei (tanpa DISTINCT)
// =====================================================================
echo PHP_EOL . "=== STEP 3: DATA MENTAH PELAPOR OPERATION MEI (RAW, NO DISTINCT) ===" . PHP_EOL;
$rawRows = SipekaFinding::query()
    ->whereIn(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"), $sipOp)
    ->whereRaw("STR_TO_DATE({$tc}, '%Y-%m-%d %H:%i') BETWEEN ? AND ?", [$start, $end])
    ->get();

echo "Total baris: " . count($rawRows) . PHP_EOL . PHP_EOL;
foreach ($rawRows as $r) {
    $d = $r->data_sipeka;
    echo sprintf(
        "  id=%-8s fn=%-25s pelapor='%s'\n",
        $r->id,
        $d['fungsi'] ?? '?',
        $d['pelapor'] ?? '?'
    );
}

// =====================================================================
// STEP 4A: DISTINCT langsung
// =====================================================================
echo PHP_EOL . "=== STEP 4A: COUNT(DISTINCT pelapor) ===" . PHP_EOL;
$distinctA = SipekaFinding::query()
    ->whereIn(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"), $sipOp)
    ->whereRaw("STR_TO_DATE({$tc}, '%Y-%m-%d %H:%i') BETWEEN ? AND ?", [$start, $end])
    ->distinct()
    ->count(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor'))"));
echo "  COUNT(DISTINCT pelapor) = {$distinctA}" . PHP_EOL;

// =====================================================================
// STEP 4B: DISTINCT setelah TRIM dan LOWER
// =====================================================================
echo PHP_EOL . "=== STEP 4B: COUNT(DISTINCT TRIM(LOWER(pelapor))) ===" . PHP_EOL;
$distinctB = SipekaFinding::query()
    ->whereIn(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"), $sipOp)
    ->whereRaw("STR_TO_DATE({$tc}, '%Y-%m-%d %H:%i') BETWEEN ? AND ?", [$start, $end])
    ->distinct()
    ->count(DB::raw("TRIM(LOWER(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor'))))"));
echo "  COUNT(DISTINCT TRIM(LOWER(pelapor))) = {$distinctB}" . PHP_EOL;

// =====================================================================
// STEP 5: Verifikasi chartKeterlibatan pakai seluruh mapping?
// =====================================================================
echo PHP_EOL . "=== STEP 5: VERIFIKASI chartKeterlibatan QUERY ===" . PHP_EOL;
echo "getSipekaValues('Operation') = [" . implode(', ', $sipOp) . "]" . PHP_EOL;
echo "Ini digunakan sebagai filter whereIn → SEMUA sub-fungsi sudah tercakup." . PHP_EOL;

// =====================================================================
// STEP 6: Tabel Diagnostik
// =====================================================================
echo PHP_EOL . "=== STEP 6: TABEL DIAGNOSTIK ===" . PHP_EOL;
echo sprintf("%-55s %s\n", "Pemeriksaan", "Jumlah");
echo str_repeat("-", 65) . PHP_EOL;
echo sprintf("%-55s %d\n", "Total temuan Operation Mei (semua sub-fungsi)", count($rawRows));
echo sprintf("%-55s %d\n", "Total baris (pelapor) mentah", count($rawRows));
echo sprintf("%-55s %d\n", "Pelapor unik tanpa TRIM", $distinctA);
echo sprintf("%-55s %d\n", "Pelapor unik dengan TRIM+LOWER", $distinctB);
echo sprintf("%-55s %d\n", "Jumlah fungsi SIPEKA yg masuk Operation", count($sipOp));

// List unique pelapor names
$rawPelapor = $rawRows->map(fn($r) => $r->data_sipeka['pelapor'] ?? null)->filter();
$uniqueNames = $rawPelapor->unique()->values();
echo sprintf("%-55s %d\n", "Pelapor unik (PHP Collection unique)", $uniqueNames->count());

echo PHP_EOL . "Daftar pelapor unik:" . PHP_EOL;
foreach ($uniqueNames as $name) {
    echo "  '{$name}'" . PHP_EOL;
}

echo PHP_EOL . "=== MENTOR REFERENCE ===" . PHP_EOL;
echo "  Operation: 32 pelapor / 62 manpower = 52%" . PHP_EOL;

echo PHP_EOL . "=== KESIMPULAN ===" . PHP_EOL;
echo "  Sistem menemukan {$uniqueNames->count()} pelapor unik untuk Operation Mei 2026." . PHP_EOL;
echo "  Mentor menunjukkan 32 pelapor untuk Operation Mei 2026." . PHP_EOL;
echo "  Selisih: " . (32 - $uniqueNames->count()) . " pelapor" . PHP_EOL;

if ($uniqueNames->count() < 32) {
    echo PHP_EOL . "  KEMUNGKINAN PENYEBAB:" . PHP_EOL;
    echo "  1. Data SIPEKA yang ada di database TIDAK LENGKAP" . PHP_EOL;
    echo "     (mentor mungkin menggunakan data langsung dari SIPEKA/Excel yang lebih baru/lengkap)" . PHP_EOL;
    echo "  2. Ada data yang belum di-import ke sistem" . PHP_EOL;
    echo "  3. Mentor menghitung dari sumber/periode yang berbeda" . PHP_EOL;
}
