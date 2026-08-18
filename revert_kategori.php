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
        $newKat = match($kat) {
            'Safe Condition' => 'Kondisi aman',
            'Unsafe Condition' => 'Kondisi tidak aman',
            'Safe Action' => 'Tindakan aman',
            'Unsafe Action' => 'Tindakan tidak aman',
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
echo "Reverted $updated records back to Indonesian.\n";
