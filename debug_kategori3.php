<?php
/**
 * Debug Stage 3 — Cari kombinasi filter yang menghasilkan Unsafe Condition=525
 * Dashboard klaim: 525, Mentor: 515
 * Kita tahu: Global tahun 2026 = 2400, Bulan 8 = 110
 */
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\MasterFunctionMapping;

$tahun = 2026;

echo "========================================================\n";
echo "INVESTIGASI LANJUTAN — Cari sumber angka 525 vs 515\n";
echo "========================================================\n\n";

// -------------------------------------------------------
// A. Cek per fungsi (apakah satu fungsi menghasilkan 525?)
// -------------------------------------------------------
echo "=== [A] COUNT Unsafe Condition PER FUNGSI (seluruh tahun 2026) ===\n";

$fungsiList = ['Operation', 'Maintenance', 'HSSE', 'Business Support'];
$kategoriList = ['Tindakan aman', 'Kondisi aman', 'Tindakan tidak aman', 'Kondisi tidak aman'];

foreach ($fungsiList as $fungsi) {
    $sipValues = MasterFunctionMapping::getSipekaValues($fungsi);
    
    $base = "SELECT COUNT(*) as cnt FROM sipeka_findings 
             WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = ?
               AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE '%2026%'";
    
    if (!empty($sipValues)) {
        $placeholders = implode(',', array_fill(0, count($sipValues), '?'));
        $base .= " AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi')) IN ({$placeholders})";
    } else {
        $base .= " AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi')) LIKE ?";
        $sipValues = ["%{$fungsi}%"];
    }
    
    echo "  Fungsi [{$fungsi}]:\n";
    $totalFungsi = 0;
    foreach ($kategoriList as $kat) {
        $params = array_merge([$kat], $sipValues);
        $c = DB::select($base, $params)[0]->cnt;
        $totalFungsi += $c;
        echo "    {$kat}: {$c}\n";
    }
    echo "    TOTAL: {$totalFungsi}\n\n";
}

// -------------------------------------------------------
// B. Cek semua kombinasi bulan yang menghasilkan 525
// -------------------------------------------------------
echo "=== [B] CARI BULAN YANG MENGHASILKAN Unsafe Condition ~525 ===\n";
for ($bln = 1; $bln <= 12; $bln++) {
    $blnPad = str_pad($bln, 2, '0', STR_PAD_LEFT);
    $lastDay = date('t', mktime(0, 0, 0, $bln, 1, $tahun));
    $start = "{$tahun}-{$blnPad}-01 00:00";
    $end   = "{$tahun}-{$blnPad}-{$lastDay} 23:59";
    
    $uc = DB::select("
        SELECT COUNT(*) as cnt FROM sipeka_findings
        WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = 'Kondisi tidak aman'
          AND STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')), '%Y-%m-%d %H:%i') BETWEEN ? AND ?
    ", [$start, $end])[0]->cnt;
    
    echo "  Bulan {$bln}: UC={$uc}\n";
}
echo "\n";

// -------------------------------------------------------
// C. Cek range kumulatif yang menghasilkan ~525
// -------------------------------------------------------
echo "=== [C] RANGE KUMULATIF JAN-x YANG MENDEKATI 525 ===\n";
for ($bln = 1; $bln <= 12; $bln++) {
    $blnPad = str_pad($bln, 2, '0', STR_PAD_LEFT);
    $lastDay = date('t', mktime(0, 0, 0, $bln, 1, $tahun));
    $start = "{$tahun}-01-01 00:00";
    $end   = "{$tahun}-{$blnPad}-{$lastDay} 23:59";
    
    $ucArr = [];
    $total = 0;
    foreach ($kategoriList as $kat) {
        $c = DB::select("
            SELECT COUNT(*) as cnt FROM sipeka_findings
            WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = ?
              AND STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')), '%Y-%m-%d %H:%i') BETWEEN ? AND ?
        ", [$kat, $start, $end])[0]->cnt;
        $ucArr[$kat] = $c;
        $total += $c;
    }
    
    $pct = $total > 0 ? round($ucArr['Kondisi tidak aman']/$total*100) : 0;
    echo "  Jan-{$bln}: UC={$ucArr['Kondisi tidak aman']} (total={$total})\n";
}
echo "\n";

// -------------------------------------------------------
// D. Cek per Fungsi + Bulan yang menghasilkan 525
// -------------------------------------------------------
echo "=== [D] PER FUNGSI + BULAN YANG MENGHASILKAN UC ~515-525 ===\n";
foreach ($fungsiList as $fungsi) {
    $sipValues = MasterFunctionMapping::getSipekaValues($fungsi);
    
    for ($bln = 1; $bln <= 8; $bln++) {
        $blnPad  = str_pad($bln, 2, '0', STR_PAD_LEFT);
        $lastDay = date('t', mktime(0, 0, 0, $bln, 1, $tahun));
        
        // YTD filter
        $start = "{$tahun}-01-01 00:00";
        $end   = "{$tahun}-{$blnPad}-{$lastDay} 23:59";
        
        if (!empty($sipValues)) {
            $placeholders = implode(',', array_fill(0, count($sipValues), '?'));
            $params = array_merge(['Kondisi tidak aman', $start, $end], $sipValues);
            $c = DB::select("
                SELECT COUNT(*) as cnt FROM sipeka_findings
                WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = ?
                  AND STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')), '%Y-%m-%d %H:%i') BETWEEN ? AND ?
                  AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi')) IN ({$placeholders})
            ", $params)[0]->cnt;
        } else {
            $c = 0;
        }
        
        if ($c >= 500 && $c <= 540) {
            echo "  *** MATCH! Fungsi={$fungsi}, Jan-Bln{$bln}: UC={$c} ***\n";
        }
    }
}
echo "\n";

// -------------------------------------------------------
// E. Lihat semua nilai fungsi di SIPEKA (raw values)
// -------------------------------------------------------
echo "=== [E] NILAI FUNGSI RAW DI DATABASE (tahun 2026) ===\n";
$fungsiRaw = DB::select("
    SELECT 
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi')) as fungsi,
        COUNT(*) as total
    FROM sipeka_findings
    WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE '%2026%'
    GROUP BY fungsi
    ORDER BY total DESC
    LIMIT 30
");
foreach ($fungsiRaw as $r) {
    echo "  '" . $r->fungsi . "' => " . $r->total . "\n";
}
echo "\n";

// -------------------------------------------------------
// F. Mapping MasterFunctionMapping — cek apa yang dipetakan
// -------------------------------------------------------
echo "=== [F] MASTER FUNCTION MAPPING ===\n";
foreach ($fungsiList as $fungsi) {
    $sipValues = MasterFunctionMapping::getSipekaValues($fungsi);
    echo "  {$fungsi}: " . json_encode($sipValues) . "\n";
}
echo "\n";

// -------------------------------------------------------
// G. Apakah ada fungsi yang tidak ter-cover mapping?
// -------------------------------------------------------
echo "=== [G] FUNGSI YANG TIDAK TER-COVER MAPPING (tahun 2026) ===\n";
$allMapped = [];
foreach ($fungsiList as $fungsi) {
    $sipValues = MasterFunctionMapping::getSipekaValues($fungsi);
    $allMapped = array_merge($allMapped, $sipValues);
}

$uncovered = DB::select("
    SELECT 
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi')) as fungsi,
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) as kategori,
        COUNT(*) as total
    FROM sipeka_findings
    WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE '%2026%'
      AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi')) NOT IN ('" . implode("','", $allMapped) . "')
    GROUP BY fungsi, kategori
    ORDER BY fungsi, total DESC
    LIMIT 30
");
if (empty($uncovered)) {
    echo "  Tidak ada. Semua fungsi ter-cover mapping.\n";
} else {
    foreach ($uncovered as $r) {
        echo "  Fungsi='{$r->fungsi}', Kategori='{$r->kategori}': {$r->total}\n";
    }
}
echo "\n";

// -------------------------------------------------------
// H. APAKAH N/A TERMASUK DALAM HITUNGAN MENTOR?
//    Total mentor ~1005, dashboard 1015 = selisih 10
//    Mungkin N/A tidak dihitung mentor, tapi N/A bukan 10
// -------------------------------------------------------
echo "=== [H] DATA N/A — APAKAH BISA MENYEBABKAN SELISIH? ===\n";
$naAll = DB::select("
    SELECT COUNT(*) as cnt FROM sipeka_findings
    WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = 'N/A'
      AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE '%2026%'
")[0]->cnt;
echo "  Total N/A tahun 2026: {$naAll}\n\n";

// -------------------------------------------------------
// I. Cek TAHUN SEBELUMNYA — apakah mentor filter 2026 saja atau semua?
// -------------------------------------------------------
echo "=== [I] TOTAL KATEGORI TANPA FILTER TAHUN ===\n";
foreach ($kategoriList as $kat) {
    $c = DB::select("
        SELECT COUNT(*) as cnt FROM sipeka_findings
        WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = ?
    ", [$kat])[0]->cnt;
    echo "  {$kat}: {$c}\n";
}
echo "\n";
