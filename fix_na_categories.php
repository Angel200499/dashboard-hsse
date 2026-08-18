<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rs = App\Models\SipekaFinding::whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = 'N/A' OR JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) IS NULL")->get();
$updated = 0;
foreach ($rs as $r) {
    $temuan = strtolower($r->data_sipeka['temuan'] ?? '');
    $cat = 'N/A';
    if (strpos($temuan, 'unsafe condition') !== false || strpos($temuan, 'unsafecondition') !== false || strpos($temuan, 'kondisi tidak aman') !== false) {
        $cat = 'Kondisi tidak aman';
    } elseif (strpos($temuan, 'safe condition') !== false || strpos($temuan, 'safecondition') !== false || strpos($temuan, 'kondisi aman') !== false) {
        $cat = 'Kondisi aman';
    } elseif (strpos($temuan, 'unsafe action') !== false || strpos($temuan, 'unsafeaction') !== false || strpos($temuan, 'tindakan tidak aman') !== false) {
        $cat = 'Tindakan tidak aman';
    } elseif (strpos($temuan, 'safe action') !== false || strpos($temuan, 'safeaction') !== false || strpos($temuan, 'tindakan aman') !== false) {
        $cat = 'Tindakan aman';
    }
    
    if ($cat !== 'N/A') {
        $d = $r->data_sipeka;
        $d['kategori'] = $cat;
        $r->data_sipeka = $d;
        $r->save();
        $updated++;
    }
}
echo "Extracted category from text for $updated N/A records.\n";
