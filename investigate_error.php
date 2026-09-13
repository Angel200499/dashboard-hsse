<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Ambil ImportLog terbaru
$log = App\Models\ImportLog::latest()->first();
echo "=== IMPORT LOG TERBARU ===\n";
echo "ID: " . $log->id . "\n";
echo "Filename: " . $log->filename . "\n";
echo "Insert: " . $log->jumlah_insert . "\n";
echo "Update: " . $log->jumlah_update . "\n";
echo "Skip: " . $log->jumlah_skip . "\n";
echo "Error: " . $log->jumlah_error . "\n";
echo "Status: " . $log->status . "\n";
echo "Catatan: " . ($log->catatan ?? '(null)') . "\n";
echo "Created at: " . $log->created_at . "\n";

// 2. Ambil semua ImportLog untuk melihat pattern
echo "\n=== 5 ImportLog TERAKHIR ===\n";
$logs = App\Models\ImportLog::latest()->take(5)->get();
foreach ($logs as $l) {
    echo "  [{$l->id}] {$l->created_at} | insert={$l->jumlah_insert} update={$l->jumlah_update} skip={$l->jumlah_skip} error={$l->jumlah_error} status={$l->status}\n";
    echo "   catatan: " . ($l->catatan ?? '(null)') . "\n";
}
