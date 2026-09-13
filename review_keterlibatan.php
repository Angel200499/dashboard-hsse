<?php
/**
 * Review Logic Keterlibatan
 * Bandingkan hasil dua interpretasi periode:
 *   A) monthQuery — pelapor unik pada BULAN YANG DIPILIH saja (existing)
 *   B) ytdQuery   — pelapor unik dari Januari s/d bulan yang dipilih (arahan mentor)
 *
 * Denominator: MasterManpower::getManpower(tahun, bulan, fungsi) — sama untuk keduanya.
 */
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\MasterFunctionMapping;
use App\Models\MasterManpower;
use App\Models\SipekaFinding;

$tahun = 2026;
$fungsiList = ['Operation', 'Maintenance', 'HSSE', 'Business Support'];
$tanggalCol = "JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal'))";

echo "=== REVIEW LOGIC KETERLIBATAN — PERBANDINGAN DUA INTERPRETASI ===\n";
echo "Tahun: $tahun | Denominator: MasterManpower::getManpower(tahun, bulan, fungsi)\n\n";

// Simulasikan setiap bulan yang dipilih user (1-8, karena hanya ada data s/d Agustus)
foreach ([6, 7, 8] as $bulan) {
    $bulanPadded = str_pad($bulan, 2, '0', STR_PAD_LEFT);
    $lastDay = date('t', mktime(0, 0, 0, $bulan, 1, $tahun));

    echo "=========================================================\n";
    echo "BULAN DIPILIH: $bulan (". ['','Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'][$bulan] .")\n";
    echo "=========================================================\n";
    printf("  %-20s | %-12s | %-12s | %-12s | %-14s | %-14s | %-12s\n",
        'Fungsi', 'Manpower', 'Unik-Bulan', 'Rate-Bulan', 'Unik-YTD', 'Rate-YTD', 'Ref Mentor');
    printf("  %-20s | %-12s | %-12s | %-12s | %-14s | %-14s | %-12s\n",
        str_repeat('-', 20), str_repeat('-', 12), str_repeat('-', 12), str_repeat('-', 12),
        str_repeat('-', 14), str_repeat('-', 14), str_repeat('-', 12));

    $refMentor = ['Operation' => '65%', 'Maintenance' => '50%', 'HSSE' => '44%', 'Business Support' => '90%'];

    foreach ($fungsiList as $f) {
        $sipValues = MasterFunctionMapping::getSipekaValues($f);
        $manpower  = MasterManpower::getManpower($tahun, $bulan, $f);

        // === EXISTING: monthQuery (bulan spesifik) ===
        $qMonth = SipekaFinding::query();
        if (!empty($sipValues)) {
            $qMonth->whereIn(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"), $sipValues);
        } else {
            $qMonth->where('data_sipeka->fungsi', 'like', "%{$f}%");
        }
        $startMonth = "{$tahun}-{$bulanPadded}-01 00:00";
        $endMonth   = "{$tahun}-{$bulanPadded}-{$lastDay} 23:59";
        $qMonth->whereRaw("STR_TO_DATE({$tanggalCol}, '%Y-%m-%d %H:%i') BETWEEN ? AND ?", [$startMonth, $endMonth]);
        $unikBulan = $qMonth->distinct()->count(
            DB::raw("NULLIF(TRIM(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor'))), '')")
        );
        $rateBulan = ($manpower > 0) ? round($unikBulan / $manpower * 100, 2) . '%' : 'N/A';

        // === ARAHAN MENTOR: ytdQuery (Jan s/d bulan) ===
        $qYtd = SipekaFinding::query();
        if (!empty($sipValues)) {
            $qYtd->whereIn(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"), $sipValues);
        } else {
            $qYtd->where('data_sipeka->fungsi', 'like', "%{$f}%");
        }
        $startYtd = "{$tahun}-01-01 00:00";
        $endYtd   = "{$tahun}-{$bulanPadded}-{$lastDay} 23:59";
        $qYtd->whereRaw("STR_TO_DATE({$tanggalCol}, '%Y-%m-%d %H:%i') BETWEEN ? AND ?", [$startYtd, $endYtd]);
        $unikYtd = $qYtd->distinct()->count(
            DB::raw("NULLIF(TRIM(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor'))), '')")
        );
        $rateYtd = ($manpower > 0) ? round($unikYtd / $manpower * 100, 2) . '%' : 'N/A';

        printf("  %-20s | %-12s | %-12s | %-12s | %-14s | %-14s | %-12s\n",
            $f,
            $manpower ?? 'N/A',
            $unikBulan,
            $rateBulan,
            $unikYtd,
            $rateYtd,
            $refMentor[$f]
        );
    }
    echo "\n";
}

echo "=== KETERANGAN KOLOM ===\n";
echo "  Manpower    : MasterManpower::getManpower(tahun, bulan, fungsi)\n";
echo "  Unik-Bulan  : COUNT(DISTINCT pelapor) pada bulan dipilih saja [EXISTING]\n";
echo "  Rate-Bulan  : Unik-Bulan / Manpower × 100 [EXISTING]\n";
echo "  Unik-YTD    : COUNT(DISTINCT pelapor) dari Januari s/d bulan dipilih [ARAHAN MENTOR]\n";
echo "  Rate-YTD    : Unik-YTD / Manpower × 100 [ARAHAN MENTOR]\n";
echo "  Ref Mentor  : Referensi angka dari dokumen mentor (bukan hardcode, hanya untuk validasi)\n\n";

echo "=== ANALISIS GAP ===\n";
echo "Perbedaan utama:\n";
echo "  EXISTING (monthQuery):\n";
echo "    → Menghitung pelapor unik HANYA pada bulan yang dipilih\n";
echo "    → Jika pilih Agustus: hanya hitung pelapor yang melapor di Agustus\n";
echo "    → Seseorang yang melapor di Juli dan Agustus = 1 pelapor (di Agustus)\n";
echo "    → Seseorang yang melapor di Januari-Juli tapi tidak di Agustus = 0 (tidak dihitung!)\n\n";
echo "  ARAHAN MENTOR (ytdQuery / kumulatif):\n";
echo "    → Menghitung pelapor unik dari Januari s/d bulan yang dipilih\n";
echo "    → Jika pilih Agustus: hitung pelapor yang pernah melapor kapanpun Jan-Agu\n";
echo "    → Seseorang yang melapor di bulan manapun Jan-Agu = 1 pelapor\n";
echo "    → Hasil lebih tinggi dan lebih representatif sebagai 'keterlibatan observasi'\n\n";

echo "=== DAMPAK PADA DENOMINATOR ===\n";
echo "Saat ini denominator = manpower bulan terpilih.\n";
echo "Jika periode numerator berubah ke YTD, denominator tetap bisa:\n";
echo "  Option 1: Manpower bulan terpilih [KONSISTEN dengan existing]\n";
echo "  Option 2: Manpower bulan terpilih [karena represents 'jumlah karyawan saat ini']\n";
echo "Kedua opsi secara teknis konsisten — manpower adalah jumlah karyawan resmi.\n";
