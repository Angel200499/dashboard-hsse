<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$vals = App\Models\SipekaFinding::selectRaw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) as cat")->distinct()->pluck('cat')->toArray();
echo json_encode($vals);
