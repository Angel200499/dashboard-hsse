<?php
/**
 * Debug Kategori PEKA — investigasi selisih 10 record Unsafe Condition
 * Dashboard: 525, Mentor: 515
 * Filter: tahun=2026
 */
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tahun = 2026;

echo "========================================================\n";
echo "INVESTIGASI KATEGORI PEKA — TAHUN {$tahun}\n";
echo "========================================================\n\n";

// -------------------------------------------------------
// 1. SEMUA NILAI KATEGORI DI DATABASE (RAW, tahun 2026)
// -------------------------------------------------------
echo "=== [1] SEMUA NILAI KATEGORI RAW (JSON_EXTRACT) — Tahun {$tahun} ===\n";
$raw = DB::select("
    SELECT 
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) as kategori,
        COUNT(*) as total
    FROM sipeka_findings
    WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE ?
    GROUP BY kategori
    ORDER BY total DESC
", ["%{$tahun}%"]);

$grandTotal = 0;
foreach ($raw as $r) {
    echo "  '" . $r->kategori . "' => " . $r->total . "\n";
    $grandTotal += $r->total;
}
echo "  GRAND TOTAL semua kategori: {$grandTotal}\n\n";

// -------------------------------------------------------
// 2. HITUNG SEPERTI chartKategoriPeka()
// -------------------------------------------------------
echo "=== [2] COUNT PER MAPPING (seperti chartKategoriPeka) ===\n";
$kategoriMap = [
    'Tindakan aman'       => 'Safe Action',
    'Kondisi aman'        => 'Safe Condition',
    'Tindakan tidak aman' => 'Unsafe Action',
    'Kondisi tidak aman'  => 'Unsafe Condition',
];

$rawByKey = [];
foreach ($raw as $r) {
    $rawByKey[$r->kategori] = $r->total;
}

$total4Kategori = 0;
foreach ($kategoriMap as $dbKey => $displayLabel) {
    $val = $rawByKey[$dbKey] ?? 0;
    echo "  {$displayLabel} ({$dbKey}): {$val}\n";
    $total4Kategori += $val;
}
echo "  Total 4 kategori: {$total4Kategori}\n\n";

// -------------------------------------------------------
// 3. PERIKSA APAKAH ADA NILAI MIRIP "Kondisi tidak aman"
// -------------------------------------------------------
echo "=== [3] VARIASI NILAI YANG MIRIP 'Kondisi tidak aman' ===\n";
$similar = DB::select("
    SELECT 
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) as kategori,
        COUNT(*) as total
    FROM sipeka_findings
    WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE ?
      AND LOWER(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori'))) LIKE '%tidak aman%'
    GROUP BY kategori
    ORDER BY total DESC
", ["%{$tahun}%"]);
foreach ($similar as $r) {
    echo "  '" . $r->kategori . "' => " . $r->total . "\n";
}
echo "\n";

// -------------------------------------------------------
// 4. CARI RECORD "Kondisi tidak aman" DENGAN TAHUN AMBIGU
//    baseQuery() menggunakan LIKE '%2026%' pada tanggal
//    → bisa terjebak jika ada nilai lain yang mengandung '2026'
// -------------------------------------------------------
echo "=== [4] ANALISIS FILTER TAHUN — LIKE '%2026%' vs YEAR() ===\n";

// Hitung dengan LIKE (cara yang dipakai saat ini)
$countLike = DB::select("
    SELECT COUNT(*) as total
    FROM sipeka_findings
    WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = 'Kondisi tidak aman'
      AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE '%2026%'
")[0]->total;

// Hitung dengan YEAR() yang lebih presisi
$countYear = DB::select("
    SELECT COUNT(*) as total
    FROM sipeka_findings
    WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = 'Kondisi tidak aman'
      AND YEAR(STR_TO_DATE(
            JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')), 
            '%Y-%m-%d %H:%i'
          )) = 2026
")[0]->total;

// Hitung dengan DATE >= dan < (range eksplisit)
$countRange = DB::select("
    SELECT COUNT(*) as total
    FROM sipeka_findings
    WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = 'Kondisi tidak aman'
      AND STR_TO_DATE(
            JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')), 
            '%Y-%m-%d %H:%i'
          ) BETWEEN '2026-01-01 00:00' AND '2026-12-31 23:59'
")[0]->total;

echo "  Kondisi tidak aman (LIKE '%2026%')             : {$countLike}\n";
echo "  Kondisi tidak aman (YEAR()=2026)               : {$countYear}\n";
echo "  Kondisi tidak aman (BETWEEN 2026-01..2026-12)  : {$countRange}\n";
$diff = $countLike - $countYear;
echo "  SELISIH LIKE vs YEAR: {$diff}\n\n";

// -------------------------------------------------------
// 5. TEMUKAN RECORD YANG MASUK LIKE TAPI TIDAK MASUK YEAR
// -------------------------------------------------------
if ($diff > 0) {
    echo "=== [5] {$diff} RECORD YANG TERHITUNG OLEH LIKE TAPI BUKAN TAHUN 2026 ===\n";
    $anomali = DB::select("
        SELECT 
            id_temuan,
            JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) as tanggal,
            JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) as kategori,
            JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi')) as fungsi,
            JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.status')) as status
        FROM sipeka_findings
        WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = 'Kondisi tidak aman'
          AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE '%2026%'
          AND YEAR(STR_TO_DATE(
                JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')), 
                '%Y-%m-%d %H:%i'
              )) != 2026
        LIMIT 50
    ");
    foreach ($anomali as $r) {
        echo "  ID: {$r->id_temuan} | tanggal: {$r->tanggal} | fungsi: {$r->fungsi} | status: {$r->status}\n";
    }
    echo "\n";
}

// -------------------------------------------------------
// 6. CEK TOTAL SEMUA KATEGORI DENGAN KEDUA METODE
// -------------------------------------------------------
echo "=== [6] TOTAL SEMUA KATEGORI — PERBANDINGAN METODE FILTER ===\n";
$kategoriList = ['Tindakan aman', 'Kondisi aman', 'Tindakan tidak aman', 'Kondisi tidak aman'];
$totalLike = 0;
$totalYear = 0;
foreach ($kategoriList as $kat) {
    $cLike = DB::select("
        SELECT COUNT(*) as total FROM sipeka_findings
        WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = ?
          AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE '%2026%'
    ", [$kat])[0]->total;

    $cYear = DB::select("
        SELECT COUNT(*) as total FROM sipeka_findings
        WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = ?
          AND YEAR(STR_TO_DATE(
                JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')), 
                '%Y-%m-%d %H:%i'
              )) = 2026
    ", [$kat])[0]->total;

    $totalLike += $cLike;
    $totalYear += $cYear;
    $d = $cLike - $cYear;
    echo "  {$kat}: LIKE={$cLike}, YEAR={$cYear}, selisih={$d}\n";
}
echo "  TOTAL LIKE: {$totalLike}\n";
echo "  TOTAL YEAR: {$totalYear}\n";
echo "  TOTAL SELISIH: " . ($totalLike - $totalYear) . "\n\n";

// -------------------------------------------------------
// 7. CARI 10 ID TEMUAN YANG MENYEBABKAN SELISIH
// -------------------------------------------------------
echo "=== [7] DAFTAR ID TEMUAN YANG MENYEBABKAN SELISIH (LIKE tapi bukan 2026) ===\n";
$anomaliAll = DB::select("
    SELECT 
        id_temuan,
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) as tanggal,
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) as kategori,
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi')) as fungsi,
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.status')) as status,
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.unsafe_conditon')) as unsafe_condition_val
    FROM sipeka_findings
    WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE '%2026%'
      AND YEAR(STR_TO_DATE(
            JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')), 
            '%Y-%m-%d %H:%i'
          )) != 2026
    ORDER BY JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal'))
    LIMIT 50
");

if (empty($anomaliAll)) {
    echo "  Tidak ditemukan record anomali dengan metode LIKE vs YEAR.\n";
    echo "  Selisih mungkin berasal dari sumber lain.\n\n";
} else {
    foreach ($anomaliAll as $r) {
        echo "  ID: {$r->id_temuan} | tanggal: '{$r->tanggal}' | kategori: '{$r->kategori}' | fungsi: '{$r->fungsi}'\n";
    }
    echo "\n";
}

// -------------------------------------------------------
// 8. PERIKSA APAKAH ADA DUPLIKAT id_temuan
// -------------------------------------------------------
echo "=== [8] CEK DUPLIKAT id_temuan DALAM sipeka_findings ===\n";
$duplikat = DB::select("
    SELECT id_temuan, COUNT(*) as cnt
    FROM sipeka_findings
    WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE '%2026%'
      AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = 'Kondisi tidak aman'
    GROUP BY id_temuan
    HAVING cnt > 1
    LIMIT 20
");
if (empty($duplikat)) {
    echo "  Tidak ada duplikat id_temuan untuk Kondisi tidak aman tahun 2026.\n";
} else {
    echo "  DUPLIKAT DITEMUKAN:\n";
    foreach ($duplikat as $d) {
        echo "  id_temuan={$d->id_temuan}, count={$d->cnt}\n";
    }
}
echo "\n";

// -------------------------------------------------------
// 9. CEK FORMAT TANGGAL UNIK YANG ADA DI DB
// -------------------------------------------------------
echo "=== [9] CONTOH FORMAT TANGGAL (20 sample, Kondisi tidak aman) ===\n";
$samples = DB::select("
    SELECT 
        id_temuan,
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) as tanggal
    FROM sipeka_findings
    WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = 'Kondisi tidak aman'
      AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE '%2026%'
    ORDER BY RAND()
    LIMIT 20
");
foreach ($samples as $s) {
    echo "  ID: {$s->id_temuan} | tanggal: '{$s->tanggal}'\n";
}
echo "\n";

// -------------------------------------------------------
// 10. APAKAH ADA NILAI 'target' ATAU 'closeby' YANG MENGANDUNG 2026
//     YANG BISA BOCOR KE FILTER TANGGAL?
// -------------------------------------------------------
echo "=== [10] CEK APAKAH FIELD LAIN MENGANDUNG '2026' (field tanggal bukan null) ===\n";
// Kita cek record yang tanggalnya tidak mengandung 2026 tapi masih lolos
$noTanggal2026 = DB::select("
    SELECT COUNT(*) as total
    FROM sipeka_findings
    WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = 'Kondisi tidak aman'
      AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) NOT LIKE '%2026%'
      AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE '%2026%'
")[0]->total;
echo "  Record lolos filter tapi tanggal tidak '2026': {$noTanggal2026} (expected 0)\n\n";

// -------------------------------------------------------
// 11. MENTOR MENGGUNAKAN FILTER BULAN (AGUSTUS 2026)?
//     Cek jika filter bulan=8 menghasilkan angka berbeda
// -------------------------------------------------------
echo "=== [11] HITUNG KATEGORI DENGAN FILTER BULAN=8 (Agustus 2026) ===\n";
foreach ($kategoriList as $kat) {
    $cMonth = DB::select("
        SELECT COUNT(*) as total FROM sipeka_findings
        WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = ?
          AND STR_TO_DATE(
                JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')), 
                '%Y-%m-%d %H:%i'
              ) BETWEEN '2026-01-01 00:00' AND '2026-08-31 23:59'
    ", [$kat])[0]->total;
    echo "  {$kat} (Jan-Aug 2026): {$cMonth}\n";
}
echo "\n";

// -------------------------------------------------------
// 12. HITUNG KATEGORI DENGAN FILTER BULAN=8 — TAHUN SAJA (baseQuery style)
// -------------------------------------------------------
echo "=== [12] SUMMARY AKHIR ===\n";
echo "  Query dashboard (LIKE '%2026%'):\n";
foreach ($kategoriMap as $dbKey => $displayLabel) {
    $c = $rawByKey[$dbKey] ?? 0;
    echo "    {$displayLabel}: {$c}\n";
}
echo "\n";
