<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Check tanggal format samples
echo "=== SAMPLE TANGGAL FORMAT ===\n";
$rows = DB::select("SELECT JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) as tgl FROM sipeka_findings LIMIT 10");
foreach ($rows as $r) {
    echo $r->tgl . "\n";
}

// Check count by month using LIKE 2026-MM
echo "\n=== COUNT BY MONTH (LIKE YYYY-MM) ===\n";
for ($m = 1; $m <= 12; $m++) {
    $pm = str_pad($m, 2, '0', STR_PAD_LEFT);
    $count = DB::select("SELECT COUNT(*) as c FROM sipeka_findings WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE '2026-{$pm}%'");
    echo "2026-{$pm}: " . $count[0]->c . "\n";
}

// Check total
echo "\n=== TOTAL RECORDS ===\n";
$total = DB::select("SELECT COUNT(*) as c FROM sipeka_findings");
echo "Total: " . $total[0]->c . "\n";

// Check manpower data
echo "\n=== MASTER MANPOWER ===\n";
$mp = DB::select("SELECT fungsi, tahun, bulan, jumlah_manpower FROM master_manpowers ORDER BY tahun, bulan, fungsi");
foreach ($mp as $r) {
    echo "{$r->fungsi} | {$r->tahun} | bulan {$r->bulan} | {$r->jumlah_manpower}\n";
}
