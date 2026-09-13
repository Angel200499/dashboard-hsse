<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tahun = 2026;

echo "=== STATUS VALUES IN DATABASE ===\n";
$statuses = DB::select("SELECT JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.status')) as status, COUNT(*) as cnt FROM sipeka_findings GROUP BY status ORDER BY cnt DESC");
foreach ($statuses as $r) {
    echo "  '" . $r->status . "' => " . $r->cnt . "\n";
}

echo "\n=== STATUS VALUES TAHUN $tahun ===\n";
$statuses26 = DB::select("SELECT JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.status')) as status, COUNT(*) as cnt FROM sipeka_findings WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE ? GROUP BY status ORDER BY cnt DESC", ["%{$tahun}%"]);
foreach ($statuses26 as $r) {
    echo "  '" . $r->status . "' => " . $r->cnt . "\n";
}

echo "\n=== KATEGORI VALUES TAHUN $tahun ===\n";
$kats = DB::select("SELECT JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) as kategori, COUNT(*) as cnt FROM sipeka_findings WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE ? GROUP BY kategori ORDER BY cnt DESC", ["%{$tahun}%"]);
$grandTotal = 0;
foreach ($kats as $r) {
    echo "  '" . $r->kategori . "' => " . $r->cnt . "\n";
    $grandTotal += $r->cnt;
}
echo "  GRAND TOTAL: $grandTotal\n";

echo "\n=== MONTHLY BREAKDOWN TAHUN $tahun (ALL KATEGORI) ===\n";
$monthly = DB::select("
    SELECT 
        MONTH(STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')), '%Y-%m-%d %H:%i')) as bulan,
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) as kategori,
        COUNT(*) as cnt
    FROM sipeka_findings
    WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE ?
    GROUP BY bulan, kategori
    ORDER BY bulan, kategori
", ["%{$tahun}%"]);
foreach ($monthly as $r) {
    echo "  Bulan " . str_pad($r->bulan, 2) . " | '" . $r->kategori . "' => " . $r->cnt . "\n";
}

echo "\n=== CLOSED COUNT TAHUN $tahun ===\n";
$closed = DB::select("
    SELECT COUNT(*) as cnt
    FROM sipeka_findings
    WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE ?
      AND LOWER(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.status'))) = 'closed'
", ["%{$tahun}%"]);
echo "  Closed: " . $closed[0]->cnt . "\n";

$total = DB::select("
    SELECT COUNT(*) as cnt
    FROM sipeka_findings
    WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE ?
", ["%{$tahun}%"]);
echo "  Total: " . $total[0]->cnt . "\n";

$rate = $total[0]->cnt > 0 ? round($closed[0]->cnt / $total[0]->cnt * 100, 2) : 0;
echo "  Closing Rate: {$rate}%\n";
