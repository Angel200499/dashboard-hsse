<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$findings = App\Models\SipekaFinding::all();
$updated = 0;

foreach ($findings as $finding) {
    $data = $finding->data_sipeka;
    if (isset($data['kategori'])) {
        $kat = trim($data['kategori']);
        $newKat = match(strtolower($kat)) {
            'kondisi aman' => 'Safe Condition',
            'kondisi tidak aman' => 'Unsafe Condition',
            'tindakan aman' => 'Safe Action',
            'tindakan tidak aman' => 'Unsafe Action',
            default => $kat
        };
        
        if ($kat !== $newKat) {
            $data['kategori'] = $newKat;
            $finding->data_sipeka = $data;
            $finding->save();
            $updated++;
        }
    }
}
echo "Updated $updated records.\n";
