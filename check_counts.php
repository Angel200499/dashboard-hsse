<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$findings = App\Models\SipekaFinding::all();
$uc = 0;
$ua = 0;
$both_null = 0;
foreach ($findings as $r) {
    $c = $r->data_sipeka['unsafe_conditon'] ?? null;
    $a = $r->data_sipeka['unsafe_action'] ?? null;
    if ($c && $c !== 'null' && $c !== '-') $uc++;
    elseif ($a && $a !== 'null' && $a !== '-') $ua++;
    else $both_null++;
}
echo "Unsafe Condition: $uc\n";
echo "Unsafe Action: $ua\n";
echo "Both Null: $both_null\n";
