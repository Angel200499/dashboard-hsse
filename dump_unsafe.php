<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$act = App\Models\SipekaFinding::selectRaw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.unsafe_action')) as act")->distinct()->pluck('act')->toArray();
$cond = App\Models\SipekaFinding::selectRaw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.unsafe_conditon')) as act")->distinct()->pluck('act')->toArray();

echo "Unsafe Actions:\n";
var_dump($act);
echo "\nUnsafe Conditions:\n";
var_dump($cond);
