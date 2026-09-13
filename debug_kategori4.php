<?php
/**
 * Debug Stage 4 — Fokus pada:
 * 1. Fungsi 'Operation' (bukan Operation LHD) yang tidak masuk mapping Operation
 * 2. 'Area Lahendong', 'Area Karaha', 'null' yang uncovered
 * 3. Bandingkan total 4 fungsi vs global
 * 4. Cari 10 record penyebab selisih
 */
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\MasterFunctionMapping;

$tahun = 2026;

echo "========================================================\n";
echo "INVESTIGASI FINAL — Uncovered Fungsi & Selisih 10 Record\n";
echo "========================================================\n\n";

// -------------------------------------------------------
// 1. HITUNG GLOBAL vs SUM 4 FUNGSI
// -------------------------------------------------------
echo "=== [1] GLOBAL vs SUM 4 FUNGSI (Unsafe Condition, 2026) ===\n";

$globalUC = DB::select("
    SELECT COUNT(*) as cnt FROM sipeka_findings
    WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = 'Kondisi tidak aman'
      AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE '%2026%'
")[0]->cnt;

echo "  Global (semua fungsi): {$globalUC}\n\n";

$fungsiList = ['Operation', 'Maintenance', 'HSSE', 'Business Support'];
$totalFungsi = 0;
foreach ($fungsiList as $fungsi) {
    $sipValues = MasterFunctionMapping::getSipekaValues($fungsi);
    if (!empty($sipValues)) {
        $placeholders = implode(',', array_fill(0, count($sipValues), '?'));
        $params = array_merge(['Kondisi tidak aman'], $sipValues);
        $c = DB::select("
            SELECT COUNT(*) as cnt FROM sipeka_findings
            WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = ?
              AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE '%2026%'
              AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi')) IN ({$placeholders})
        ", $params)[0]->cnt;
    } else {
        $c = 0;
    }
    $totalFungsi += $c;
    echo "  {$fungsi}: {$c}\n";
}
echo "  SUM 4 fungsi: {$totalFungsi}\n";
echo "  SELISIH (Global - Sum4Fungsi): " . ($globalUC - $totalFungsi) . "\n\n";

// -------------------------------------------------------
// 2. RECORD YANG TIDAK MASUK KE MAPPING MANAPUN
//    (ini yang menyebabkan selisih antara global dan sum 4 fungsi)
// -------------------------------------------------------
echo "=== [2] RECORD 'Kondisi tidak aman' YANG TIDAK TER-COVER MAPPING ===\n";

$allMapped = [];
foreach ($fungsiList as $f) {
    $allMapped = array_merge($allMapped, MasterFunctionMapping::getSipekaValues($f));
}
$allMapped = array_unique($allMapped);

$placeholders = implode(',', array_fill(0, count($allMapped), '?'));
$uncoveredRecords = DB::select("
    SELECT 
        id_temuan,
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi')) as fungsi,
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) as kategori,
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) as tanggal,
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.status')) as status,
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.unsafe_conditon')) as unsafe_condition_val
    FROM sipeka_findings
    WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = 'Kondisi tidak aman'
      AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE '%2026%'
      AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi')) NOT IN ({$placeholders})
    ORDER BY JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi')), id_temuan
", $allMapped);

echo "  Total record tidak ter-cover: " . count($uncoveredRecords) . "\n\n";
foreach ($uncoveredRecords as $r) {
    echo "  ID: {$r->id_temuan} | fungsi: '{$r->fungsi}' | tanggal: {$r->tanggal} | status: {$r->status}\n";
    echo "         kategori: '{$r->kategori}' | unsafe_condition_raw: '{$r->unsafe_condition_val}'\n";
}
echo "\n";

// -------------------------------------------------------
// 3. KHUSUS FUNGSI 'Operation' (bukan 'Operation LHD')
//    Apakah harus masuk ke mapping Operation?
// -------------------------------------------------------
echo "=== [3] DETAIL FUNGSI 'Operation' YANG TIDAK DI MAPPING ===\n";
$opNotMapped = DB::select("
    SELECT 
        id_temuan,
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi')) as fungsi,
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) as kategori,
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) as tanggal,
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.status')) as status
    FROM sipeka_findings
    WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi')) = 'Operation'
      AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE '%2026%'
    ORDER BY id_temuan
    LIMIT 50
");
echo "  Total record dengan fungsi='Operation' tahun 2026: " . count($opNotMapped) . "\n";
foreach ($opNotMapped as $r) {
    echo "  ID: {$r->id_temuan} | kategori: '{$r->kategori}' | tanggal: {$r->tanggal} | status: {$r->status}\n";
}
echo "\n";

// -------------------------------------------------------
// 4. SEKARANG KITA TAHU: 10 RECORD YANG MENJADI SELISIH
//    Dashboard Global menghitung uncovered fungsi
//    Mentor mungkin melihat sum dari 4 fungsi yang terdefinisi
// -------------------------------------------------------
echo "=== [4] RINGKASAN SELISIH PER KATEGORI (Global vs Sum4Fungsi) ===\n";
$kategoriMap = [
    'Tindakan aman'       => 'Safe Action',
    'Kondisi aman'        => 'Safe Condition',
    'Tindakan tidak aman' => 'Unsafe Action',
    'Kondisi tidak aman'  => 'Unsafe Condition',
];

foreach ($kategoriMap as $dbKey => $displayLabel) {
    // Global
    $gTotal = DB::select("
        SELECT COUNT(*) as cnt FROM sipeka_findings
        WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = ?
          AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE '%2026%'
    ", [$dbKey])[0]->cnt;

    // Sum 4 fungsi
    $fTotal = 0;
    foreach ($fungsiList as $fungsi) {
        $sipValues = MasterFunctionMapping::getSipekaValues($fungsi);
        if (!empty($sipValues)) {
            $ph = implode(',', array_fill(0, count($sipValues), '?'));
            $params = array_merge([$dbKey], $sipValues);
            $c = DB::select("
                SELECT COUNT(*) as cnt FROM sipeka_findings
                WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = ?
                  AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE '%2026%'
                  AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi')) IN ({$ph})
            ", $params)[0]->cnt;
            $fTotal += $c;
        }
    }

    $selisih = $gTotal - $fTotal;
    echo "  {$displayLabel}: Global={$gTotal}, Sum4Fungsi={$fTotal}, Selisih={$selisih}\n";
}

echo "\n";

// -------------------------------------------------------
// 5. KONFIRMASI: BERAPA TOTAL MENTOR SEHARUSNYA?
//    Jika mentor melihat sum dari 4 fungsi (misal per fungsi)
// -------------------------------------------------------
echo "=== [5] TOTAL SUM 4 FUNGSI KESELURUHAN (kemungkinan yang mentor lihat) ===\n";
$total4 = 0;
foreach ($kategoriMap as $dbKey => $displayLabel) {
    $fTotal = 0;
    foreach ($fungsiList as $fungsi) {
        $sipValues = MasterFunctionMapping::getSipekaValues($fungsi);
        if (!empty($sipValues)) {
            $ph = implode(',', array_fill(0, count($sipValues), '?'));
            $params = array_merge([$dbKey], $sipValues);
            $c = DB::select("
                SELECT COUNT(*) as cnt FROM sipeka_findings
                WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = ?
                  AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE '%2026%'
                  AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi')) IN ({$ph})
            ", $params)[0]->cnt;
            $fTotal += $c;
        }
    }
    $total4 += $fTotal;
    echo "  {$displayLabel}: {$fTotal}\n";
}
echo "  TOTAL: {$total4}\n\n";

// -------------------------------------------------------
// 6. KONTEKS: MENTOR MUNGKIN MELIHAT DATA YANG BERBEDA
//    Hipotesis: Mentor melihat 1 bulan TERTENTU, bukan full year
//    Kita tahu total mentor ~1005, 515 UC
// -------------------------------------------------------
echo "=== [6] HIPOTESIS: MENTOR MELIHAT DATA BULAN TERTENTU ===\n";
echo "  Jika mentor melihat bulan=2 (Februari), tahun=2026:\n";
for ($bln = 1; $bln <= 8; $bln++) {
    $blnPad = str_pad($bln, 2, '0', STR_PAD_LEFT);
    $lastDay = date('t', mktime(0, 0, 0, $bln, 1, $tahun));
    $start = "{$tahun}-{$blnPad}-01 00:00";
    $end   = "{$tahun}-{$blnPad}-{$lastDay} 23:59";

    $total = 0;
    $ucVal = 0;
    foreach (array_keys($kategoriMap) as $dbKey) {
        $c = DB::select("
            SELECT COUNT(*) as cnt FROM sipeka_findings
            WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = ?
              AND STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')), '%Y-%m-%d %H:%i') BETWEEN ? AND ?
        ", [$dbKey, $start, $end])[0]->cnt;
        $total += $c;
        if ($dbKey === 'Kondisi tidak aman') $ucVal = $c;
    }
    echo "  Bulan {$bln}: total={$total}, UC={$ucVal}\n";
}
echo "\n";
