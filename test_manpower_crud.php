<?php

/**
 * Test CRUD + Validation untuk MasterManpower
 * Jalankan: php artisan tinker test_manpower_crud.php
 */

use App\Models\MasterManpower;

$pass = 0;
$fail = 0;

function test(string $label, bool $condition): void {
    global $pass, $fail;
    if ($condition) {
        echo "  ✅ PASS — {$label}\n";
        $pass++;
    } else {
        echo "  ❌ FAIL — {$label}\n";
        $fail++;
    }
}

// Bersihkan data test sebelumnya jika ada
MasterManpower::whereIn('fungsi', ['Operation', 'Maintenance'])->where('tahun', 9999)->delete();
MasterManpower::where('fungsi', 'Operation')->where('tahun', 9998)->delete();

echo "\n=== TEST CRUD MASTER MANPOWER ===\n\n";

// -----------------------------------------
// Test 1: CREATE valid
// -----------------------------------------
echo "[1] CREATE\n";
$mp = MasterManpower::create([
    'fungsi' => 'Operation',
    'tahun' => 9999,
    'jumlah_manpower' => 150
]);
test('Create record berhasil', $mp->exists);
test('Fungsi tersimpan: Operation', $mp->fungsi === 'Operation');
test('Tahun tersimpan: 9999', $mp->tahun === 9999);
test('Jumlah tersimpan: 150', $mp->jumlah_manpower === 150);

// -----------------------------------------
// Test 2: READ
// -----------------------------------------
echo "\n[2] READ\n";
$found = MasterManpower::where('fungsi', 'Operation')->where('tahun', 9999)->first();
test('Read record berhasil', $found !== null);
test('Data fungsi benar', $found && $found->fungsi === 'Operation');

// -----------------------------------------
// Test 3: UPDATE
// -----------------------------------------
echo "\n[3] UPDATE\n";
$found->update(['jumlah_manpower' => 175]);
$refreshed = MasterManpower::find($found->id);
test('Update jumlah_manpower berhasil', $refreshed->jumlah_manpower === 175);
test('Fungsi tidak berubah', $refreshed->fungsi === 'Operation');

// -----------------------------------------
// Test 4: UNIQUE CONSTRAINT — duplicate harus ditolak
// -----------------------------------------
echo "\n[4] UNIQUE CONSTRAINT\n";
$duplicateRejected = false;
try {
    MasterManpower::create([
        'fungsi' => 'Operation',
        'tahun' => 9999,
        'jumlah_manpower' => 999
    ]);
} catch (\Illuminate\Database\QueryException $e) {
    $duplicateRejected = true;
}
test('Duplicate fungsi+tahun ditolak database', $duplicateRejected);

// -----------------------------------------
// Test 5: Tahun berbeda, fungsi sama → BOLEH
// -----------------------------------------
echo "\n[5] DIFFERENT YEAR\n";
$mp2 = MasterManpower::create([
    'fungsi' => 'Operation',
    'tahun' => 9998,
    'jumlah_manpower' => 100
]);
test('Fungsi sama, tahun berbeda: berhasil', $mp2->exists);

// -----------------------------------------
// Test 6: Fungsi berbeda, tahun sama → BOLEH
// -----------------------------------------
echo "\n[6] DIFFERENT FUNGSI\n";
$mp3 = MasterManpower::create([
    'fungsi' => 'Maintenance',
    'tahun' => 9999,
    'jumlah_manpower' => 200
]);
test('Tahun sama, fungsi berbeda: berhasil', $mp3->exists);

// -----------------------------------------
// Test 7: availableYears() helper
// -----------------------------------------
echo "\n[7] availableYears()\n";
$years = MasterManpower::availableYears();
test('availableYears() returns collection', $years instanceof \Illuminate\Support\Collection);
test('Year 9999 ada di list', $years->contains(9999));

// -----------------------------------------
// Test 8: byTahun scope
// -----------------------------------------
echo "\n[8] SCOPES\n";
$filtered = MasterManpower::byTahun(9999)->get();
test('byTahun(9999) mengembalikan 2 record', $filtered->count() === 2);

$filteredFn = MasterManpower::byFungsi('Operation')->where('tahun', 9999)->get();
test('byFungsi(Operation) + tahun 9999 = 1 record', $filteredFn->count() === 1);

// -----------------------------------------
// Test 9: DELETE
// -----------------------------------------
echo "\n[9] DELETE\n";
$idBefore = $refreshed->id;
$refreshed->delete();
$mp2->delete();
$mp3->delete();
$afterDelete = MasterManpower::find($idBefore);
test('Delete berhasil', $afterDelete === null);

// -----------------------------------------
// REGRESSION CHECK
// -----------------------------------------
echo "\n[10] REGRESSION\n";
$sfCount = DB::table('sipeka_findings')->count();
test('sipeka_findings tetap 4376', $sfCount === 4376);
$mpLeft = MasterManpower::where('tahun', 9999)->orWhere('tahun', 9998)->count();
test('Data test sudah dihapus semua', $mpLeft === 0);

// -----------------------------------------
// SUMMARY
// -----------------------------------------
echo "\n=================================\n";
echo "  PASS: {$pass} | FAIL: {$fail}\n";
echo "  " . ($fail === 0 ? '✅ SEMUA TEST BERHASIL' : '❌ ADA TEST YANG GAGAL') . "\n";
echo "=================================\n\n";
