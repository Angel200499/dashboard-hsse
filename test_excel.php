<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$f = storage_path('app/private/sipeka_imports/Test Data Angel - Magang BS 20.xlsx');
if (file_exists($f)) {
    $a = \Maatwebsite\Excel\Facades\Excel::toArray(new \App\Imports\SipekaFindingsImport, $f);
    echo json_encode(array_keys($a[0][0]));
} else {
    echo "No file: $f";
}
