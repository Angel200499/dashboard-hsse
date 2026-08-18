<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$findings = App\Models\SipekaFinding::all();
$na = 0;
foreach ($findings as $r) {
    if (($r->data_sipeka['kategori'] ?? '') === 'N/A') $na++;
}
echo "Real N/A count: $na\n";
