<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$r = App\Models\SipekaFinding::latest()->limit(5)->get();
foreach ($r as $item) {
    echo "ID: " . $item->id_temuan . "\n";
    echo "Kategori: " . ($item->data_sipeka['kategori'] ?? 'null') . "\n";
    echo "Hazard: " . ($item->data_sipeka['hazard'] ?? 'null') . "\n";
    echo "Unsafe Action: " . ($item->data_sipeka['unsafe_action'] ?? 'null') . "\n";
    echo "Unsafe Condition: " . ($item->data_sipeka['unsafe_conditon'] ?? 'null') . "\n";
    echo "------------------\n";
}
