<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$keys = [];
$findings = App\Models\SipekaFinding::all();
foreach ($findings as $finding) {
    if (is_array($finding->data_sipeka)) {
        foreach (array_keys($finding->data_sipeka) as $k) {
            $keys[$k] = true;
        }
    }
}
echo json_encode(array_keys($keys));
