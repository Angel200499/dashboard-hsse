<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rs = App\Models\SipekaFinding::whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = 'N/A'")->limit(10)->get();
foreach ($rs as $r) {
    echo $r->data_sipeka['temuan'] . "\n";
}
