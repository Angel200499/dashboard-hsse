<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$r = App\Models\SipekaFinding::latest()->first();
echo json_encode(array_keys($r->data_sipeka));
$r2 = App\Models\SipekaFinding::whereRaw("JSON_EXTRACT(data_sipeka, '$.kategori') = 'N/A'")->first();
if ($r2) {
    echo "\nN/A Record Keys: " . json_encode(array_keys($r2->data_sipeka));
}
$r3 = App\Models\SipekaFinding::whereRaw("JSON_EXTRACT(data_sipeka, '$.kategori_peka') IS NOT NULL")->first();
if ($r3) {
    echo "\nKategori Peka Record Keys: " . json_encode(array_keys($r3->data_sipeka));
}
$r4 = App\Models\SipekaFinding::where('data_sipeka', 'like', '%kategori_temuan%')->first();
if ($r4) {
    echo "\nKategori Temuan Record Keys: " . json_encode(array_keys($r4->data_sipeka));
}
$allKeys = collect(App\Models\SipekaFinding::all()->pluck('data_sipeka'))->flatMap(function($item) { return array_keys((array)$item); })->unique()->values()->toArray();
echo "\nAll Keys: " . json_encode($allKeys);
