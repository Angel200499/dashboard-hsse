<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->boot();

use Illuminate\Support\Facades\DB;
use App\Models\MasterManpower;

echo "=== INVESTIGASI REPORTING RATE DESEMBER 2026 ===" . PHP_EOL . PHP_EOL;

// Query 1: MAX tanggal 2026
$tanggalCol = "JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal'))";
$max = DB::select("SELECT MAX(STR_TO_DATE({$tanggalCol}, '%Y-%m-%d %H:%i')) as max_tanggal FROM sipeka_findings WHERE {$tanggalCol} LIKE '2026%'");
echo "MAX tanggal 2026: " . ($max[0]->max_tanggal ?? 'null') . PHP_EOL . PHP_EOL;

// Query 2: Count per bulan 2026
$perBulan = DB::select("SELECT 
    MONTH(STR_TO_DATE({$tanggalCol}, '%Y-%m-%d %H:%i')) AS bulan,
    COUNT(*) AS jumlah_record,
    COUNT(DISTINCT id_temuan) AS jumlah_distinct_id,
    MAX(STR_TO_DATE({$tanggalCol}, '%Y-%m-%d %H:%i')) AS max_tanggal
FROM sipeka_findings 
WHERE {$tanggalCol} LIKE '2026%' 
GROUP BY bulan 
ORDER BY bulan");

$bulanNames = [
    1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April',
    5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus',
    9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'
];

echo "=== DATA TEMUAN PER BULAN 2026 ===" . PHP_EOL;
$dataBulan = [];
foreach ($perBulan as $row) {
    $dataBulan[$row->bulan] = $row;
}
for ($b = 1; $b <= 12; $b++) {
    if (isset($dataBulan[$b])) {
        $r = $dataBulan[$b];
        echo "  Bulan {$b} ({$bulanNames[$b]}): {$r->jumlah_record} record, {$r->jumlah_distinct_id} distinct id_temuan, MAX: {$r->max_tanggal}" . PHP_EOL;
    } else {
        echo "  Bulan {$b} ({$bulanNames[$b]}): TIDAK ADA DATA" . PHP_EOL;
    }
}

// Query 3: Data September-Desember secara spesifik
echo PHP_EOL . "=== CEK SPESIFIK SEPTEMBER–DESEMBER 2026 ===" . PHP_EOL;
for ($b = 9; $b <= 12; $b++) {
    $bulanPad = str_pad($b, 2, '0', STR_PAD_LEFT);
    $rows = DB::select("SELECT id_temuan, {$tanggalCol} as tanggal FROM sipeka_findings WHERE {$tanggalCol} LIKE '2026-{$bulanPad}%' LIMIT 5");
    echo "  Bulan {$b} ({$bulanNames[$b]}): " . count($rows) . " sample records" . PHP_EOL;
    foreach ($rows as $r) {
        echo "    id_temuan={$r->id_temuan}, tanggal={$r->tanggal}" . PHP_EOL;
    }
}

// Query 4: Cek manpower 2026
echo PHP_EOL . "=== DATA MANPOWER 2026 ===" . PHP_EOL;
$manpowers = DB::select("SELECT tahun, bulan, fungsi, jumlah_manpower FROM master_manpowers WHERE tahun = 2026 ORDER BY bulan, fungsi");
if (empty($manpowers)) {
    echo "  Tidak ada data manpower untuk 2026!" . PHP_EOL;
} else {
    foreach ($manpowers as $mp) {
        echo "  Tahun:{$mp->tahun} Bulan:{$mp->bulan} Fungsi:{$mp->fungsi} Manpower:{$mp->jumlah_manpower}" . PHP_EOL;
    }
}

// Cek MAX bulan manpower 2026
$maxMp = DB::select("SELECT MAX(bulan) as max_bulan FROM master_manpowers WHERE tahun = 2026");
echo PHP_EOL . "  MAX bulan manpower 2026: " . ($maxMp[0]->max_bulan ?? 'null') . PHP_EOL;

// Query 5: Cek manpower December 2026
echo PHP_EOL . "=== MANPOWER DESEMBER (Bulan 12) 2026 ===" . PHP_EOL;
$mpDec = DB::select("SELECT * FROM master_manpowers WHERE tahun = 2026 AND bulan = 12");
if (empty($mpDec)) {
    echo "  TIDAK ADA manpower untuk Desember 2026" . PHP_EOL;
} else {
    foreach ($mpDec as $mp) {
        echo "  Fungsi:{$mp->fungsi} Manpower:{$mp->jumlah_manpower}" . PHP_EOL;
    }
}

// Konfirmasi: getManpower method
echo PHP_EOL . "=== CEK MasterManpower::getManpower untuk Desember 2026 ===" . PHP_EOL;
$fungsiList = ['Operation', 'Maintenance', 'HSSE', 'Business Support'];
foreach ($fungsiList as $f) {
    $mp = MasterManpower::getManpower(2026, 12, $f);
    echo "  Fungsi:{$f} => " . ($mp === null ? 'NULL (tidak tersedia)' : $mp) . PHP_EOL;
}

echo PHP_EOL . "=== SELESAI ===" . PHP_EOL;
