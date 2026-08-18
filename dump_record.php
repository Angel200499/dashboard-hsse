<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$r2 = App\Models\SipekaFinding::whereRaw("JSON_EXTRACT(data_sipeka, '$.kategori') = 'N/A'")->first();
if ($r2) echo json_encode($r2->data_sipeka, JSON_PRETTY_PRINT);
