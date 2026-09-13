<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tahun = 2026;

echo "=== CEK NILAI unsafe_conditon UNTUK KATEGORI 'Kondisi tidak aman' ===\n";

// Apakah ada record yang berisi unsafe_conditon tidak kosong/null
$notNull = DB::select("
    SELECT COUNT(*) as cnt FROM sipeka_findings 
    WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = 'Kondisi tidak aman'
      AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE '%2026%'
      AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.unsafe_conditon')) IS NOT NULL 
      AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.unsafe_conditon')) != 'null'
")[0]->cnt;
echo "UC dengan unsafe_conditon tidak null: {$notNull}\n";

$isNull = DB::select("
    SELECT COUNT(*) as cnt FROM sipeka_findings 
    WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = 'Kondisi tidak aman'
      AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE '%2026%'
      AND (JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.unsafe_conditon')) IS NULL 
           OR JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.unsafe_conditon')) = 'null')
")[0]->cnt;
echo "UC dengan unsafe_conditon null: {$isNull}\n\n";

// Nilai distinct unsafe_conditon untuk kategori Kondisi tidak aman
echo "Nilai distinct unsafe_conditon (top 20):\n";
$rows = DB::select("
    SELECT 
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.unsafe_conditon')) as val, 
        COUNT(*) as cnt 
    FROM sipeka_findings 
    WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = 'Kondisi tidak aman'
      AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE '%2026%'
    GROUP BY val 
    ORDER BY cnt DESC 
    LIMIT 20
");
foreach ($rows as $r) {
    echo "  " . json_encode($r->val) . ": " . $r->cnt . "\n";
}
echo "\n";

// HIPOTESIS BARU: Apakah mentor memakai filter khusus?
// Mungkin mentor menggunakan DISTINCT id_temuan? Cek apakah ada duplikat
echo "=== CEK DISTINCT vs COUNT ALL ===\n";
$countAll = DB::select("
    SELECT COUNT(*) as cnt FROM sipeka_findings 
    WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = 'Kondisi tidak aman'
      AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE '%2026%'
")[0]->cnt;
$countDistinct = DB::select("
    SELECT COUNT(DISTINCT id_temuan) as cnt FROM sipeka_findings 
    WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) = 'Kondisi tidak aman'
      AND JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE '%2026%'
")[0]->cnt;
echo "COUNT(*): {$countAll}\n";
echo "COUNT(DISTINCT id_temuan): {$countDistinct}\n";
echo "Selisih: " . ($countAll - $countDistinct) . "\n\n";

// INVESTIGASI: Apakah 525 dan 515 bisa dari HALAMAN PDF/REPORT yang berbeda?
// Mari cek apakah ada controller lain yang menghasilkan data serupa
echo "=== TOTAL DATA PER TAHUN (tanpa filter 2026) ===\n";
$years = DB::select("
    SELECT 
        YEAR(STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')), '%Y-%m-%d %H:%i')) as tahun,
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) as kategori,
        COUNT(*) as total
    FROM sipeka_findings
    WHERE JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) IN ('Tindakan aman', 'Kondisi aman', 'Tindakan tidak aman', 'Kondisi tidak aman')
    GROUP BY tahun, kategori
    ORDER BY tahun DESC, total DESC
");
foreach ($years as $r) {
    echo "  Tahun={$r->tahun} | {$r->kategori}: {$r->total}\n";
}
echo "\n";

// HIPOTESIS: Screenshot mentor mungkin dari SESI BERBEDA SEBELUM IMPORT TERAKHIR
// Sebelum import terakhir (2026-09-09), ada 6260 data, sekarang 6276
// Selisih 16 data baru
// Apakah 16 data baru ini mengandung Unsafe Condition?
echo "=== 16 DATA TERBARU YANG BARU DIINSERT ===\n";
$newest = DB::select("
    SELECT 
        id_temuan,
        created_at,
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) as kategori,
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) as tanggal,
        JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi')) as fungsi
    FROM sipeka_findings
    ORDER BY created_at DESC
    LIMIT 20
");
foreach ($newest as $r) {
    echo "  ID: {$r->id_temuan} | created: {$r->created_at} | kategori: '{$r->kategori}' | tanggal: {$r->tanggal} | fungsi: {$r->fungsi}\n";
}
echo "\n";

// Berapa banyak dari 16 terbaru yang merupakan 'Kondisi tidak aman'?
$newUC = DB::select("
    SELECT COUNT(*) as cnt FROM (
        SELECT id_temuan, JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) as kategori
        FROM sipeka_findings
        ORDER BY created_at DESC
        LIMIT 16
    ) t
    WHERE kategori = 'Kondisi tidak aman'
")[0]->cnt;
echo "Dari 16 terbaru yang UC: {$newUC}\n";
