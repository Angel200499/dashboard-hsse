<?php
// Verification script for GM special group mapping PR
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MasterFunctionMapping;
use Illuminate\Support\Facades\DB;

echo "=== SCHEMA: master_function_mappings columns ===" . PHP_EOL;
$cols = DB::select('SHOW COLUMNS FROM master_function_mappings');
foreach ($cols as $col) {
    echo "  {$col->Field} | {$col->Type} | Null: {$col->Null} | Default: " . ($col->Default ?? 'NULL') . PHP_EOL;
}

echo PHP_EOL . "=== AREA LAHENDONG RECORD ===" . PHP_EOL;
$areaLhd = MasterFunctionMapping::where('fungsi_sipeka', 'Area Lahendong')->first();
if ($areaLhd) {
    echo "  fungsi_sipeka   : {$areaLhd->fungsi_sipeka}" . PHP_EOL;
    echo "  fungsi_dashboard: " . ($areaLhd->fungsi_dashboard ?? 'NULL') . PHP_EOL;
    echo "  kelompok_khusus : " . ($areaLhd->kelompok_khusus ?? 'NULL') . PHP_EOL;
} else {
    echo "  NOT FOUND" . PHP_EOL;
}

echo PHP_EOL . "=== getSipekaValues() per 4 fungsi utama ===" . PHP_EOL;
foreach (['Operation', 'Maintenance', 'HSSE', 'Business Support'] as $fungsi) {
    $vals = MasterFunctionMapping::getSipekaValues($fungsi);
    $hasAreaLhd = in_array('Area Lahendong', $vals);
    echo "  [{$fungsi}]: " . count($vals) . " values — contains 'Area Lahendong'? " . ($hasAreaLhd ? 'YES (BUG!)' : 'No (correct)') . PHP_EOL;
}

echo PHP_EOL . "=== getGmSipekaValues() ===" . PHP_EOL;
$gmVals = MasterFunctionMapping::getGmSipekaValues();
echo "  GM values: " . implode(', ', $gmVals) . PHP_EOL;
echo "  Area Lahendong in GM? " . (in_array('Area Lahendong', $gmVals) ? 'YES (correct)' : 'No (BUG!)') . PHP_EOL;

echo PHP_EOL . "=== UNMAPPED FUNGSI ===" . PHP_EOL;
$unmapped = MasterFunctionMapping::unmappedFungsi();
echo "  Count: " . $unmapped->count() . PHP_EOL;
echo "  Values: " . implode(', ', $unmapped->toArray()) . PHP_EOL;
$areaLhdUnmapped = $unmapped->contains('Area Lahendong');
echo "  Area Lahendong still unmapped? " . ($areaLhdUnmapped ? 'YES (BUG!)' : 'No (correct)') . PHP_EOL;

echo PHP_EOL . "=== DASHBOARD_FUNGSI_LIST ===" . PHP_EOL;
print_r(MasterFunctionMapping::DASHBOARD_FUNGSI_LIST);

echo PHP_EOL . "=== KELOMPOK_KHUSUS_LIST ===" . PHP_EOL;
print_r(MasterFunctionMapping::KELOMPOK_KHUSUS_LIST);

echo PHP_EOL . "=== ALL CHECKS COMPLETE ===" . PHP_EOL;
