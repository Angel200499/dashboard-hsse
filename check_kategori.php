<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$result = DB::select("SELECT JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) as kategori, COUNT(*) as total FROM sipeka_findings GROUP BY kategori ORDER BY total DESC LIMIT 20");
echo "=== Nilai Kategori di Database ===\n";
foreach($result as $r) {
    echo "  '" . $r->kategori . "' => " . $r->total . "\n";
}
