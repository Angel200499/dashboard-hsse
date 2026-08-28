<?php

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->boot();

// Cek struktur tabel master_manpowers
echo "=== STRUKTUR TABEL master_manpowers ===\n";
$cols = DB::select('DESCRIBE master_manpowers');
foreach ($cols as $c) {
    echo sprintf("  %-20s %-40s Key:%-4s Null:%s\n", $c->Field, $c->Type, $c->Key, $c->Null);
}

// Cek unique constraint
echo "\n=== UNIQUE CONSTRAINT ===\n";
$idx = DB::select("SHOW INDEX FROM master_manpowers WHERE Key_name = 'unique_fungsi_tahun'");
echo "  unique_fungsi_tahun: " . count($idx) . " column(s) registered\n";
foreach ($idx as $i) {
    echo "  Column: " . $i->Column_name . " | Unique: " . ($i->Non_unique == 0 ? 'YES' : 'NO') . "\n";
}

// Regression: sipeka_findings count
echo "\n=== REGRESSION CHECK ===\n";
$mpCount = DB::table('master_manpowers')->count();
$sfCount = DB::table('sipeka_findings')->count();
echo "  master_manpowers count : {$mpCount} (expected: 0 — kosong)\n";
echo "  sipeka_findings count  : {$sfCount} (expected: 4376)\n";
echo "  Regression " . ($sfCount === 4376 ? 'PASSED' : 'FAILED — count berubah!') . "\n";
