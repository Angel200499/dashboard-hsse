<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rs = App\Models\SipekaFinding::get();
$updated = 0;
foreach ($rs as $r) {
    if (isset($r->data_sipeka['kategori'])) {
        $cat = $r->data_sipeka['kategori'];
        // If it was changed from N/A to something else based on temuan text, revert it.
        // Wait, how do we know if it was N/A in excel?
        // We can check if `temuan` contains the category string, AND `unsafe_action` and `unsafe_conditon` are null, 
        // AND it's one of the records we might have touched.
        // Or simply, we can just revert all records where `unsafe_action` and `unsafe_conditon` are null, and the category matches what we set.
        // BUT wait, some records might legit have "Kondisi aman" in Excel AND null in unsafe_action!
        // To be safe, we can just say: if `hazard` == 'No Categories', then it was N/A in excel!
        // Let's check `hazard`!
        if (isset($r->data_sipeka['hazard']) && $r->data_sipeka['hazard'] === 'No Categories' && $cat !== 'N/A') {
            $d = $r->data_sipeka;
            $d['kategori'] = 'N/A';
            $r->data_sipeka = $d;
            $r->save();
            $updated++;
        }
    }
}
echo "Reverted $updated N/A records.\n";
