<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== 1. TOTAL RECORD ===" . PHP_EOL;
echo "Total: " . DB::table('sipeka_findings')->count() . PHP_EOL . PHP_EOL;

echo "=== 2. KOLOM id_temuan — CEK NULL / KOSONG ===" . PHP_EOL;
$nullIdTemuan = DB::table('sipeka_findings')
    ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.idtemuan')) IS NULL
        OR TRIM(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.idtemuan'))) = ''
        OR JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.idtemuan')) = 'null'")
    ->count();
echo "Record dengan id_temuan NULL/kosong: {$nullIdTemuan}" . PHP_EOL . PHP_EOL;

echo "=== 3. DUPLIKAT id_temuan ===" . PHP_EOL;
$dupIdTemuan = DB::table('sipeka_findings')
    ->selectRaw("id_temuan, COUNT(*) as jumlah")
    ->whereNotNull('id_temuan')
    ->where('id_temuan', '!=', '')
    ->groupBy('id_temuan')
    ->havingRaw("COUNT(*) > 1")
    ->orderBy('jumlah', 'desc')
    ->limit(20)
    ->get();

if ($dupIdTemuan->isEmpty()) {
    echo "Tidak ada duplikat id_temuan di kolom dedicated." . PHP_EOL;
} else {
    echo "DUPLIKAT DITEMUKAN:" . PHP_EOL;
    foreach ($dupIdTemuan as $r) {
        echo "  id_temuan: {$r->id_temuan} → {$r->jumlah}x" . PHP_EOL;
    }
}
echo PHP_EOL;

echo "=== 4. CEK KOLOM id_temuan DI TABEL (dedicated column) ===" . PHP_EOL;
// Cek apakah kolom id_temuan ada sebagai kolom dedicated
$columns = DB::getSchemaBuilder()->getColumnListing('sipeka_findings');
echo "Kolom di tabel: " . implode(', ', $columns) . PHP_EOL . PHP_EOL;

echo "=== 5. DISTRIBUSI IMPORT LOG ===" . PHP_EOL;
$logs = DB::table('import_logs')
    ->select('id', 'filename', 'jumlah_insert', 'jumlah_update', 'jumlah_skip', 'jumlah_error', 'status', 'created_at')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();
foreach ($logs as $log) {
    echo "  [{$log->created_at}] {$log->filename}" . PHP_EOL;
    echo "    Insert: {$log->jumlah_insert} | Update: {$log->jumlah_update} | Skip: {$log->jumlah_skip} | Error: {$log->jumlah_error} | Status: {$log->status}" . PHP_EOL;
}
echo PHP_EOL;

echo "=== 6. SAMPLE id_temuan dari data_sipeka (JSON) ===" . PHP_EOL;
$samples = DB::table('sipeka_findings')
    ->selectRaw("id, id_temuan, created_at, JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.idtemuan')) as idtemuan_json")
    ->orderBy('id', 'asc')
    ->limit(5)
    ->get();
foreach ($samples as $r) {
    echo "  ID: {$r->id} | id_temuan(col): [{$r->id_temuan}] | idtemuan(json): [{$r->idtemuan_json}] | created: {$r->created_at}" . PHP_EOL;
}
echo PHP_EOL;

echo "=== 7. RECORD DI LUAR RENTANG IMPORT TERBARU ===" . PHP_EOL;
// Import terbaru: 2026-08-28 05:44
// Cari record yang id_temuan-nya tidak muncul di import hari ini
// Alias: record lama yang tidak di-update (tidak ada di Excel terbaru)
$oldRecords = DB::table('sipeka_findings')
    ->selectRaw("id, id_temuan, created_at, updated_at, JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi')) as fungsi")
    ->where('created_at', '<', '2026-08-28 00:00:00')
    ->where('updated_at', '<', '2026-08-28 00:00:00') // tidak diupdate saat import terakhir
    ->count();
echo "Record yang TIDAK masuk/diupdate oleh import 2026-08-28: {$oldRecords}" . PHP_EOL . PHP_EOL;

echo "=== 8. BREAKDOWN RECORD LAMA YANG TIDAK DIUPDATE ===" . PHP_EOL;
$oldByDate = DB::table('sipeka_findings')
    ->selectRaw("DATE(created_at) as created, DATE(updated_at) as updated, COUNT(*) as jumlah")
    ->where('created_at', '<', '2026-08-28 00:00:00')
    ->where('updated_at', '<', '2026-08-28 00:00:00')
    ->groupByRaw("DATE(created_at), DATE(updated_at)")
    ->orderBy('created', 'asc')
    ->get();
foreach ($oldByDate as $r) {
    echo "  created: {$r->created} | last_updated: {$r->updated} | jumlah: {$r->jumlah}" . PHP_EOL;
}
