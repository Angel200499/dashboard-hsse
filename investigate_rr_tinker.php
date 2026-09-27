<?php
// Script investigasi Reporting Rate - READ ONLY, tidak mengubah database
// Jalankan via: php artisan tinker < investigate_rr_tinker.php

use Illuminate\Support\Facades\DB;
use App\Models\MasterManpower;

$tc = "JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal'))";

echo "\n=== INVESTIGASI REPORTING RATE DESEMBER 2026 ===\n\n";

// 1. MAX tanggal 2026
$max = DB::select("SELECT MAX(STR_TO_DATE({$tc}, '%Y-%m-%d %H:%i')) as max_tanggal FROM sipeka_findings WHERE {$tc} LIKE '2026%'");
echo "MAX tanggal 2026: " . ($max[0]->max_tanggal ?? 'null') . "\n\n";

// 2. Count per bulan 2026
$perBulan = DB::select("SELECT 
    MONTH(STR_TO_DATE({$tc}, '%Y-%m-%d %H:%i')) AS bulan,
    COUNT(*) AS jumlah_record,
    COUNT(DISTINCT id_temuan) AS jumlah_distinct_id,
    MAX(STR_TO_DATE({$tc}, '%Y-%m-%d %H:%i')) AS max_tanggal
FROM sipeka_findings 
WHERE {$tc} LIKE '2026%' 
GROUP BY bulan 
ORDER BY bulan");

$bn = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
$db = [];
foreach ($perBulan as $row) { $db[$row->bulan] = $row; }

echo "=== DATA TEMUAN PER BULAN 2026 ===\n";
for ($b = 1; $b <= 12; $b++) {
    if (isset($db[$b])) {
        $r = $db[$b];
        echo "  Bulan $b ({$bn[$b]}): {$r->jumlah_record} record, {$r->jumlah_distinct_id} distinct id_temuan, MAX: {$r->max_tanggal}\n";
    } else {
        echo "  Bulan $b ({$bn[$b]}): TIDAK ADA DATA\n";
    }
}

// 3. Spesifik Sep-Des
echo "\n=== CEK SPESIFIK SEPTEMBER-DESEMBER 2026 ===\n";
for ($b = 9; $b <= 12; $b++) {
    $bp = str_pad($b, 2, '0', STR_PAD_LEFT);
    $rows = DB::select("SELECT id_temuan, {$tc} as tanggal FROM sipeka_findings WHERE {$tc} LIKE '2026-{$bp}%' LIMIT 3");
    echo "  Bulan $b ({$bn[$b]}): " . count($rows) . " sample\n";
    foreach ($rows as $r) {
        echo "    id_temuan={$r->id_temuan}, tanggal={$r->tanggal}\n";
    }
}

// 4. Manpower 2026
echo "\n=== DATA MANPOWER 2026 ===\n";
$mps = DB::select("SELECT tahun, bulan, fungsi, jumlah_manpower FROM master_manpowers WHERE tahun = 2026 ORDER BY bulan, fungsi");
if (empty($mps)) {
    echo "  TIDAK ADA data manpower 2026!\n";
} else {
    foreach ($mps as $mp) {
        echo "  Tahun:{$mp->tahun} Bulan:{$mp->bulan} ({$bn[$mp->bulan]}) Fungsi:{$mp->fungsi} => {$mp->jumlah_manpower}\n";
    }
}
$maxMp = DB::select("SELECT MAX(bulan) as max_bulan FROM master_manpowers WHERE tahun = 2026");
echo "  MAX bulan manpower 2026: " . ($maxMp[0]->max_bulan ?? 'null') . "\n";

// 5. getManpower Desember 2026
echo "\n=== MasterManpower::getManpower Desember 2026 ===\n";
foreach (['Operation','Maintenance','HSSE','Business Support'] as $f) {
    $mp = MasterManpower::getManpower(2026, 12, $f);
    echo "  $f => " . ($mp === null ? 'NULL (tidak tersedia)' : $mp) . "\n";
}

echo "\n=== SELESAI ===\n";
