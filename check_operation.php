<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MasterFunctionMapping;

// Tampilkan mapping yang sudah ada
echo '=== Mapping yang sudah ada di database ===' . PHP_EOL;
$existing = MasterFunctionMapping::orderBy('fungsi_dashboard')->orderBy('fungsi_sipeka')->get();
foreach ($existing as $m) {
    echo '  [' . $m->fungsi_sipeka . '] → ' . $m->fungsi_dashboard . PHP_EOL;
}
echo PHP_EOL;

// Cek apakah "Operation" (mixed case) masih muncul di unmapped
echo '=== unmappedFungsi() — nilai terkait "operation" ===' . PHP_EOL;
$unmapped = MasterFunctionMapping::unmappedFungsi();
$opRelated = $unmapped->filter(fn($v) => stripos($v, 'operation') !== false);

if ($opRelated->isEmpty()) {
    echo '  Tidak ada nilai terkait "operation" di unmapped list.' . PHP_EOL;
    echo '  Fix BERHASIL: "Operation" tidak muncul karena "OPERATION" sudah dipetakan.' . PHP_EOL;
} else {
    echo '  Masih ada nilai terkait "operation" di unmapped:' . PHP_EOL;
    foreach ($opRelated as $v) {
        echo '  - [' . $v . ']' . PHP_EOL;
    }
}

echo PHP_EOL . '=== Total unmapped: ' . $unmapped->count() . ' fungsi ===' . PHP_EOL;
foreach ($unmapped as $u) {
    echo '  - ' . $u . PHP_EOL;
}
