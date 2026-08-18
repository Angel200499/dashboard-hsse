<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$r = App\Models\SipekaFinding::where('data_sipeka', 'LIKE', '%Kondisi aman%')->orWhere('data_sipeka', 'LIKE', '%Tindakan aman%')->first();
if ($r) echo json_encode($r->data_sipeka, JSON_PRETTY_PRINT);
