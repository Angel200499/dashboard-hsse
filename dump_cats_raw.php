<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$r = App\Models\SipekaFinding::selectRaw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) as kat")->distinct()->pluck('kat')->toArray();
var_dump($r);
