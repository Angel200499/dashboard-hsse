<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$r = App\Models\SipekaFinding::latest()->first();
echo json_encode($r->data_sipeka, JSON_PRETTY_PRINT);
