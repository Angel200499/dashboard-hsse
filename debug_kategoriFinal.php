<?php
/**
 * Debug Stage FINAL — Rekonstruksi data SEBELUM import terakhir (2026-09-09 00:30:40)
 * Jika mentor screenshot diambil SEBELUM import ini, total UC mungkin 525
 */
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tahun = 2026;

echo "========================================================\n";
echo "REKONSTRUKSI DATA SEBELUM IMPORT 2026-09-09\n";
echo "========================================================\n\n";

$kategoriMap = [
    'Tindakan aman'       => 'Safe Action',
    'Kondisi aman'        => 'Safe Condition',
    'Tindakan tidak aman' => 'Unsafe Action',
    'Kondisi tidak aman'  => 'Unsafe Condition',
];

// Import terakhir: 2026-09-09 00:30:40
// Import sebelumnya: 2026-08-28 05:44:35
// Coba hitung data SEBELUM import 2026-09-09

echo "=== [1] DATA SEBELUM IMPORT 2026-09-09 00:30:40 ===\n";
$total1 = 0;
foreach ($kategoriMap as $dbKey => $displayLabel) {
    $c = DB::select("
        SELECT COUNT(*) as cnt FROM sipeka_findings
        WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = ?
          AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE '%2026%'
          AND created_at < '2026-09-09 00:30:40'
    ", [$dbKey])[0]->cnt;
    $total1 += $c;
    echo "  {$displayLabel}: {$c}\n";
}
echo "  TOTAL: {$total1}\n\n";

echo "=== [2] DATA SEBELUM IMPORT 2026-08-28 05:44:35 ===\n";
$total2 = 0;
foreach ($kategoriMap as $dbKey => $displayLabel) {
    $c = DB::select("
        SELECT COUNT(*) as cnt FROM sipeka_findings
        WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = ?
          AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE '%2026%'
          AND created_at < '2026-08-28 05:44:35'
    ", [$dbKey])[0]->cnt;
    $total2 += $c;
    echo "  {$displayLabel}: {$c}\n";
}
echo "  TOTAL: {$total2}\n\n";

echo "=== [3] SEMUA TIMESTAMP IMPORT (created_at unik) ===\n";
$timestamps = DB::select("
    SELECT DISTINCT DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') as ts, COUNT(*) as cnt
    FROM sipeka_findings
    GROUP BY ts
    ORDER BY ts DESC
    LIMIT 20
");
foreach ($timestamps as $t) {
    echo "  {$t->ts}: {$t->cnt} records\n";
}
echo "\n";

echo "=== [4] SEMUA TIMESTAMP DAN TOTAL UC SEBELUM TIMESTAMP ===\n";
$importTimes = DB::select("
    SELECT DISTINCT DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') as ts
    FROM sipeka_findings
    ORDER BY ts DESC
    LIMIT 20
");

foreach ($importTimes as $t) {
    $ucBefore = DB::select("
        SELECT COUNT(*) as cnt FROM sipeka_findings
        WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = 'Kondisi tidak aman'
          AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE '%2026%'
          AND created_at < ?
    ", [$t->ts])[0]->cnt;
    
    $totalBefore = 0;
    foreach (array_keys($kategoriMap) as $kat) {
        $c = DB::select("
            SELECT COUNT(*) as cnt FROM sipeka_findings
            WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = ?
              AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE '%2026%'
              AND created_at < ?
        ", [$kat, $t->ts])[0]->cnt;
        $totalBefore += $c;
    }
    
    $pct = $totalBefore > 0 ? round($ucBefore/$totalBefore*100) : 0;
    echo "  Sebelum {$t->ts}: UC={$ucBefore}/{$totalBefore} ({$pct}%)\n";
}
echo "\n";

// Cek record yang created_at = 2026-08-28 05:44:35 (insert baru waktu import itu)
echo "=== [5] RECORD YANG DI-INSERT PADA IMPORT 2026-08-28 ===\n";
$newInAug = DB::select("
    SELECT 
        id_temuan,
        created_at,
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) as kategori,
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) as tanggal
    FROM sipeka_findings
    WHERE created_at = '2026-08-28 05:44:35'
    ORDER BY id_temuan
    LIMIT 50
");
echo "  Jumlah record inserted 2026-08-28: " . count($newInAug) . "\n";
$ucCount = 0;
foreach ($newInAug as $r) {
    if ($r->kategori === 'Kondisi tidak aman') $ucCount++;
}
echo "  Dari itu yang UC: {$ucCount}\n\n";

// Cek record yang created_at = 2026-09-09 00:30:40 (insert baru waktu import terakhir)
echo "=== [6] RECORD YANG DI-INSERT PADA IMPORT 2026-09-09 ===\n";
$newInSep = DB::select("
    SELECT 
        id_temuan,
        created_at,
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) as kategori,
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) as tanggal,
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi')) as fungsi
    FROM sipeka_findings
    WHERE created_at = '2026-09-09 00:30:40'
    ORDER BY id_temuan
");
echo "  Jumlah record inserted 2026-09-09: " . count($newInSep) . "\n";
$ucSep = 0;
foreach ($newInSep as $r) {
    if ($r->kategori === 'Kondisi tidak aman') $ucSep++;
    echo "  ID:{$r->id_temuan} | kategori:'{$r->kategori}' | tanggal:{$r->tanggal} | fungsi:{$r->fungsi}\n";
}
echo "  Yang UC: {$ucSep}\n\n";

// FINAL: Apakah 2400 - 16 = 2384? atau lebih kecil?
echo "=== [7] PERHITUNGAN: SEBELUM IMPORT TERAKHIR ===\n";
echo "  UC total saat ini: 2400\n";
echo "  UC yang masuk import 2026-09-09: {$ucSep}\n";
echo "  UC sebelum import 2026-09-09 (perkiraan): " . (2400 - $ucSep) . "\n";
echo "\n";
echo "  KESIMPULAN: Angka 525 TIDAK MUNGKIN berasal dari query DB ini\n";
echo "  dengan filter apapun (bulan, fungsi, tahun).\n";
echo "  Kemungkinan 525 adalah angka dari versi/filter yang berbeda\n";
echo "  atau dari screenshot yang diambil sebelum update data tertentu.\n";
