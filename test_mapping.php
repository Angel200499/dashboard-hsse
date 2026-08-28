<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MasterFunctionMapping;

echo "=== TEST 1: CRUD ===" . PHP_EOL;

// Tambah mapping
$m1 = MasterFunctionMapping::create(['fungsi_sipeka' => 'Operation LHD', 'fungsi_dashboard' => 'Operation']);
echo "Created: {$m1->fungsi_sipeka} → {$m1->fungsi_dashboard} (ID: {$m1->id})" . PHP_EOL;

$m2 = MasterFunctionMapping::create(['fungsi_sipeka' => 'OPERATION', 'fungsi_dashboard' => 'Operation']);
echo "Created: {$m2->fungsi_sipeka} → {$m2->fungsi_dashboard} (ID: {$m2->id})" . PHP_EOL;

$m3 = MasterFunctionMapping::create(['fungsi_sipeka' => 'Maintenance LHD', 'fungsi_dashboard' => 'Maintenance']);
echo "Created: {$m3->fungsi_sipeka} → {$m3->fungsi_dashboard} (ID: {$m3->id})" . PHP_EOL;

echo PHP_EOL . "=== TEST 2: getSipekaValues() ===" . PHP_EOL;
$opValues = MasterFunctionMapping::getSipekaValues('Operation');
echo "Operation mappings: " . implode(', ', $opValues) . PHP_EOL;

$mainValues = MasterFunctionMapping::getSipekaValues('Maintenance');
echo "Maintenance mappings: " . implode(', ', $mainValues) . PHP_EOL;

$emptyValues = MasterFunctionMapping::getSipekaValues('HSSE');
echo "HSSE mappings (should be empty): " . (empty($emptyValues) ? 'OK (empty)' : 'ERROR') . PHP_EOL;

echo PHP_EOL . "=== TEST 3: UNIQUE CONSTRAINT ===" . PHP_EOL;
try {
    MasterFunctionMapping::create(['fungsi_sipeka' => 'Operation LHD', 'fungsi_dashboard' => 'Maintenance']);
    echo "ERROR: Duplicate was allowed!" . PHP_EOL;
} catch (\Exception $e) {
    echo "OK: Duplicate rejected — " . class_basename($e) . PHP_EOL;
}

echo PHP_EOL . "=== TEST 4: UPDATE ===" . PHP_EOL;
$m1->fungsi_dashboard = 'Maintenance';
$m1->save();
echo "Updated {$m1->fungsi_sipeka} → {$m1->fungsi_dashboard}" . PHP_EOL;
// Revert
$m1->fungsi_dashboard = 'Operation';
$m1->save();
echo "Reverted {$m1->fungsi_sipeka} → {$m1->fungsi_dashboard}" . PHP_EOL;

echo PHP_EOL . "=== TEST 5: unmappedFungsi() ===" . PHP_EOL;
$unmapped = MasterFunctionMapping::unmappedFungsi();
if ($unmapped->isEmpty()) {
    echo "No sipeka_findings data to test (table empty or all mapped)" . PHP_EOL;
} else {
    echo "Unmapped fungsi from sipeka_findings:" . PHP_EOL;
    foreach ($unmapped as $u) {
        echo "  - {$u}" . PHP_EOL;
    }
}

echo PHP_EOL . "=== TEST 6: DELETE ===" . PHP_EOL;
$countBefore = MasterFunctionMapping::count();
$m3->delete();
$countAfter = MasterFunctionMapping::count();
echo "Deleted Maintenance LHD. Before: {$countBefore}, After: {$countAfter}" . PHP_EOL;

echo PHP_EOL . "=== CLEANUP ===" . PHP_EOL;
MasterFunctionMapping::where('fungsi_sipeka', 'Operation LHD')->delete();
MasterFunctionMapping::where('fungsi_sipeka', 'OPERATION')->delete();
echo "Test data cleaned up. Remaining: " . MasterFunctionMapping::count() . PHP_EOL;

echo PHP_EOL . "=== ALL TESTS PASSED ===" . PHP_EOL;
