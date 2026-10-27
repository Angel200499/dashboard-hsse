<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SipekaFinding;
use App\Models\MasterFunctionMapping;
use App\Models\MasterManpower;
use App\Services\DashboardChartService;
use App\Services\FindingQueryService;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class DashboardFunctionController extends Controller
{
    public function __construct(
        private readonly DashboardChartService $chartService,
        private readonly FindingQueryService   $queryService
    ) {}

    public function index(Request $request, ?string $nama_fungsi = null)
    {
        $user = auth()->user();

        // -----------------------------------------------------------------
        // Tentukan fungsi yang akan ditampilkan
        // -----------------------------------------------------------------
        if ($user->isHsseRole()) {
            // Admin HSSE / Manager HSSE bisa akses semua fungsi via URL
            $fungsi = $nama_fungsi ?? $user->fungsi;
        } else {
            // Admin Function / Manager Function hanya bisa akses fungsi sendiri
            $fungsi = $user->fungsi;
            if ($nama_fungsi && strtolower($nama_fungsi) !== strtolower($fungsi)) {
                abort(403, 'Anda hanya dapat mengakses Dashboard Fungsi Anda sendiri.');
            }
        }

        // Normalisasi nama fungsi (case-insensitive matching)
        $validFunctions = ['Operation', 'Maintenance', 'HSSE', 'Business Support'];
        $matched        = false;
        foreach ($validFunctions as $vf) {
            if (strtolower($vf) === strtolower($fungsi)) {
                $fungsi  = $vf;
                $matched = true;
                break;
            }
        }

        if (!$matched && $fungsi) {
            abort(404, 'Fungsi tidak ditemukan.');
        }

        // -----------------------------------------------------------------
        // Validasi filter tahun
        // -----------------------------------------------------------------
        $tahunRaw     = $request->get('year');
        $tahun        = ($tahunRaw && preg_match('/^\d{4}$/', $tahunRaw)) ? (int) $tahunRaw : null;
        $selectedYear = $tahun;

        // -----------------------------------------------------------------
        // Validasi filter bulan (parameter 'month', 1-12)
        // Hanya aktif jika tahun juga dipilih
        // -----------------------------------------------------------------
        $bulanRaw      = $request->get('month');
        $bulan         = ($bulanRaw && is_numeric($bulanRaw) && (int) $bulanRaw >= 1 && (int) $bulanRaw <= 12)
                         ? (int) $bulanRaw
                         : null;

        // -----------------------------------------------------------------
        // VALIDASI PERIODE REPORTING RATE
        // Ambil max bulan SIPEKA aktual untuk tahun yang dipilih.
        // Jika user mengirim bulan melebihi max (mis. via URL bypass),
        // clamp ke max bulan yang valid.
        // -----------------------------------------------------------------
        $maxBulanSipeka = $tahun ? SipekaFinding::maxBulanTahun($tahun) : null;

        if ($bulan !== null && $maxBulanSipeka !== null && $bulan > $maxBulanSipeka) {
            $bulan = $maxBulanSipeka;
        }

        $selectedMonth = $bulan;

        // -----------------------------------------------------------------
        // FILTER MANDIRI & DEFAULT OTOMATIS: KETERLIBATAN OBSERVASI (CARD 4)
        // -----------------------------------------------------------------
        // 1. Tahun Keterlibatan (ko_year):
        //    Jika user mengirim ko_year, gunakan.
        //    Jika tidak ada ko_year, default ke tahun berjalan (now()->year).
        $koYearRaw = $request->get('ko_year');
        if ($koYearRaw !== null && preg_match('/^\d{4}$/', $koYearRaw)) {
            $koYear = (int) $koYearRaw;
        } else {
            // Default dinamis: tahun berjalan
            $koYear = (int) now()->year;
        }

        // 2. Bulan terakhir yang memiliki data SIPEKA untuk fungsi ini pada tahun $koYear
        $koMaxBulan = SipekaFinding::maxBulanTahunFungsi($koYear, $fungsi);

        // 3. Bulan Keterlibatan (ko_month):
        //    - Jika user mengirim parameter ko_month:
        //      * Angka 1-12: hormati pilihan pengguna
        //      * Kosong (""): user sengaja memilih "Semua Bulan"
        //    - Jika TIDAK ADA parameter ko_month di request (halaman baru dibuka):
        //      * Default otomatis ke bulan data SIPEKA terakhir ($koMaxBulan)
        if ($request->has('ko_month')) {
            $koMonthRaw = $request->get('ko_month');
            if ($koMonthRaw !== null && $koMonthRaw !== '' && is_numeric($koMonthRaw) && (int) $koMonthRaw >= 1 && (int) $koMonthRaw <= 12) {
                $koMonth = (int) $koMonthRaw;
            } else {
                $koMonth = null; // User eksplisit memilih "Semua Bulan"
            }
        } else {
            // Default otomatis dari data aktual
            $koMonth = $koMaxBulan;
        }

        // 4. Evaluasi status ketersediaan data untuk Card 4:
        //    - 'no_sipeka'  : data temuan SIPEKA pada tahun/bulan ini belum tersedia
        //    - 'no_month'   : user sengaja memilih "Semua Bulan"
        //    - 'no_manpower': data SIPEKA ada, tapi manpower belum diinput / 0
        //    - 'ok'         : data SIPEKA dan manpower tersedia
        $koStatus = 'ok';
        if ($koMaxBulan === null) {
            // Tahun yang dipilih sama sekali tidak memiliki data SIPEKA untuk fungsi ini
            $koStatus = 'no_sipeka';
        } elseif ($request->has('ko_month') && ($request->get('ko_month') === '' || $request->get('ko_month') === null)) {
            // User secara eksplisit memilih "Semua Bulan"
            $koStatus = 'no_month';
        } elseif ($koMonth === null) {
            $koStatus = 'no_month';
        } elseif ($koMonth > $koMaxBulan) {
            // Bulan yang dipilih melebihi data SIPEKA aktual
            $koStatus = 'no_sipeka';
        } else {
            $mp = MasterManpower::getManpower($koYear, $koMonth, $fungsi);
            if ($mp === null || $mp === 0) {
                $koStatus = 'no_manpower';
            }
        }

        // -----------------------------------------------------------------
        // FILTER MANDIRI & DEFAULT OTOMATIS: REPORTING RATE PER FUNGSI (CARD 2)
        // -----------------------------------------------------------------
        // 1. Tahun Reporting Rate (rr_year):
        //    Jika user mengirim rr_year, gunakan.
        //    Jika tidak ada rr_year, default ke tahun berjalan (now()->year).
        $rrYearRaw = $request->get('rr_year');
        if ($rrYearRaw !== null && preg_match('/^\d{4}$/', $rrYearRaw)) {
            $rrYear = (int) $rrYearRaw;
        } else {
            // Default dinamis: tahun berjalan
            $rrYear = (int) now()->year;
        }

        // 2. Bulan terakhir yang memiliki data SIPEKA untuk fungsi ini pada tahun $rrYear
        $rrMaxBulan = SipekaFinding::maxBulanTahunFungsi($rrYear, $fungsi);

        // 3. Bulan Reporting Rate (rr_month):
        //    - Jika user mengirim parameter rr_month:
        //      * Angka 1-12: hormati pilihan pengguna
        //      * Kosong (""): user sengaja memilih "Semua Bulan" (mode distribusi)
        //    - Jika TIDAK ADA parameter rr_month di request (halaman baru dibuka):
        //      * Default otomatis ke bulan data SIPEKA terakhir ($rrMaxBulan)
        if ($request->has('rr_month')) {
            $rrMonthRaw = $request->get('rr_month');
            if ($rrMonthRaw !== null && $rrMonthRaw !== '' && is_numeric($rrMonthRaw) && (int) $rrMonthRaw >= 1 && (int) $rrMonthRaw <= 12) {
                $rrMonth = (int) $rrMonthRaw;
            } else {
                $rrMonth = null; // User eksplisit memilih "Semua Bulan"
            }
        } else {
            // Default otomatis dari data aktual
            $rrMonth = $rrMaxBulan;
        }

        // 4. Evaluasi status ketersediaan data untuk Card 2:
        //    - 'no_sipeka'  : data temuan SIPEKA pada tahun/bulan ini belum tersedia
        //    - 'no_manpower': data SIPEKA ada, tapi manpower belum diinput / 0
        //    - 'all_months' : user sengaja memilih "Semua Bulan" (mode distribusi)
        //    - 'ok'         : data SIPEKA dan manpower tersedia
        $rrStatus = 'ok';
        if ($rrMaxBulan === null) {
            // Tahun yang dipilih sama sekali tidak memiliki data SIPEKA untuk fungsi ini
            $rrStatus = 'no_sipeka';
        } elseif ($request->has('rr_month') && ($request->get('rr_month') === '' || $request->get('rr_month') === null)) {
            // User secara eksplisit memilih "Semua Bulan"
            $rrStatus = 'all_months';
        } elseif ($rrMonth === null) {
            $rrStatus = 'no_sipeka';
        } elseif ($rrMonth > $rrMaxBulan) {
            // Bulan yang dipilih melebihi data SIPEKA aktual
            $rrStatus = 'no_sipeka';
        } else {
            $mp = MasterManpower::getManpower($rrYear, $rrMonth, $fungsi);
            if ($mp === null || $mp === 0) {
                $rrStatus = 'no_manpower';
            }
        }

        // -----------------------------------------------------------------
        // Mapping-aware filter helper
        // Jika mapping tersedia → whereIn; jika belum → fallback LIKE
        // -----------------------------------------------------------------
        $sipValues  = MasterFunctionMapping::getSipekaValues($fungsi);
        $applyFungsiFilter = function ($query) use ($fungsi, $sipValues) {
            if (!empty($sipValues)) {
                $query->whereIn(
                    DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"),
                    $sipValues
                );
            } else {
                // Fallback: mapping belum diinput Admin HSSE
                $query->where('data_sipeka->fungsi', 'like', "%{$fungsi}%");
            }
            return $query;
        };

        // -----------------------------------------------------------------
        // KPI — difilter by $fungsi + $tahun (jika ada)
        // KPI TIDAK berubah saat user search/filter tabel (query terpisah)
        // -----------------------------------------------------------------
        $kpiBase = $applyFungsiFilter(SipekaFinding::query());

        if ($tahun) {
            $kpiBase->whereRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE ?",
                ["%{$tahun}%"]
            );
        }

        $total = (clone $kpiBase)->count();

        $closed = (clone $kpiBase)->whereRaw(
            "LOWER(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.status'))) = 'closed'"
        )->count();

        $inProgress = (clone $kpiBase)->whereRaw(
            "LOWER(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.status'))) = 'open'"
        )->whereNotNull('no_notifikasi_sap')
         ->where('no_notifikasi_sap', '!=', '')
         ->count();

        $open = max(0, $total - $closed - $inProgress);

        $kpi = [
            'total'       => $total,
            'open'        => $open,
            'in_progress' => $inProgress,
            'closed'      => $closed,
        ];

        // -----------------------------------------------------------------
        // Charts — DashboardChartService yang sama dengan Global Dashboard
        // Reporting Rate dikontrol mandiri via $rrYear dan $rrMonth
        // Keterlibatan Observasi (Card 4) dikontrol mandiri via $koYear dan $koMonth
        // Chart lain tidak terpengaruh oleh $rrMonth / $koMonth / $bulan
        // -----------------------------------------------------------------
        $charts = $this->chartService->getCharts($fungsi, $tahun, $bulan, null, $koYear, $koMonth, $rrYear, $rrMonth, true);

        // Proteksi Reporting Rate Card 2:
        // Jika periode SIPEKA atau manpower tidak tersedia (rrStatus === 'no_sipeka' atau 'no_manpower'),
        // pastikan dataset fungsi ini bernilai null agar tidak menampilkan angka kalkulasi parsial/menyesatkan.
        if ($rrStatus === 'no_sipeka' || $rrStatus === 'no_manpower') {
            $charts['reporting']['data'][$fungsi] = null;
        }

        // -----------------------------------------------------------------
        // Tabel — FindingQueryService
        // Search + Filter Status + Filter Tahun + Sort + Pagination bersamaan
        // Difilter by $fungsi terlebih dahulu menggunakan mapping-aware filter
        // -----------------------------------------------------------------
        $tableQuery        = $applyFungsiFilter(SipekaFinding::query());
        $findingsPaginated = $this->queryService->paginate($tableQuery, $request);

        return view('pages.dashboard-fungsi', compact(
            'kpi',
            'charts',
            'findingsPaginated',
            'fungsi',
            'selectedYear',
            'selectedMonth',
            'maxBulanSipeka',   // batas atas bulan Reporting Rate berdasarkan data SIPEKA aktual
            'koYear',
            'koMonth',
            'koStatus',
            'koMaxBulan',
            'rrYear',
            'rrMonth',
            'rrStatus',
            'rrMaxBulan'
        ));
    }

    // -----------------------------------------------------------------
    // EXPORT PDF — Rekap Pelapor per Fungsi
    // -----------------------------------------------------------------

    /**
     * Generate dan download PDF Rekap Pelapor per fungsi.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string|null               $nama_fungsi
     * @return \Illuminate\Http\Response
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function exportReporterPdf(Request $request, ?string $nama_fungsi = null)
    {
        $user = auth()->user();
        $fungsi = $this->resolveFungsi($nama_fungsi, $user);

        if (!$this->canAccessRekapPelapor($user, $fungsi)) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengunduh PDF Rekap Pelapor fungsi ' . $fungsi . '.');
        }

        $rekapPeriode = (string) $request->get('rekap_periode', '');
        $rekapBulan   = (int) $request->get('rekap_bulan', now()->month);
        $rekapTahun   = (int) $request->get('rekap_tahun', now()->year);
        $rekapSearch  = (string) $request->get('rekap_search', '');
        $rekapSearch  = trim($rekapSearch);

        $rekapPelapor = $this->chartService->getFunctionReporterRecap(
            $fungsi,
            $rekapPeriode,
            $rekapBulan,
            $rekapTahun,
            $rekapSearch
        );

        $pdfView = view()->exists('pdf.rekap-pelapor')
            ? 'pdf.rekap-pelapor'
            : 'pdf.business-support-rekap-pelapor';

        $pdf = Pdf::loadView($pdfView, [
            'fungsi'        => $fungsi,
            'rekapPelapor'  => $rekapPelapor,
            'rekapPeriode'  => $rekapPeriode,
            'rekapBulan'    => $rekapBulan,
            'rekapTahun'    => $rekapTahun,
            'rekapSearch'   => $rekapSearch,
            'generatedAt'   => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        // Nama file dinamis berdasarkan fungsi dan periode
        $filename = $this->buildPdfFilename($fungsi, $rekapPeriode, $rekapBulan, $rekapTahun);

        return $pdf->download($filename);
    }

    /**
     * Generate dan download PDF Rekap Pelapor Business Support (backward compatibility).
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function exportBusinessSupportReporterPdf(Request $request)
    {
        return $this->exportReporterPdf($request, 'Business Support');
    }

    // -----------------------------------------------------------------
    // HALAMAN REKAP PELAPOR — Per Fungsi (halaman tersendiri)
    // -----------------------------------------------------------------

    /**
     * Halaman Rekap Pelapor per fungsi.
     *
     * Accessible oleh:
     *   - Superadmin (global access ke semua fungsi)
     *   - Admin Function & Manager Function sesuai fungsinya
     *   - Admin HSSE & Manager HSSE untuk fungsi HSSE
     */
    public function rekapPelapor(Request $request, ?string $nama_fungsi = null)
    {
        $user = auth()->user();
        $fungsi = $this->resolveFungsi($nama_fungsi, $user);

        if (!$this->canAccessRekapPelapor($user, $fungsi)) {
            abort(403, 'Anda tidak memiliki hak akses ke Rekap Pelapor fungsi ' . $fungsi . '.');
        }

        $rekapPeriode = (string) $request->get('rekap_periode', '');
        $rekapBulan   = (int) $request->get('rekap_bulan', now()->month);
        $rekapTahun   = (int) $request->get('rekap_tahun', now()->year);
        $rekapSearch  = (string) $request->get('rekap_search', '');
        $rekapSearch  = trim($rekapSearch);

        $rekapPelapor = $this->chartService->getFunctionReporterRecap(
            $fungsi,
            $rekapPeriode,
            $rekapBulan,
            $rekapTahun,
            $rekapSearch
        );

        $viewName = view()->exists('pages.rekap-pelapor')
            ? 'pages.rekap-pelapor'
            : 'pages.business-support.rekap-pelapor';

        return view($viewName, compact(
            'fungsi',
            'rekapPelapor',
            'rekapPeriode',
            'rekapBulan',
            'rekapTahun',
            'rekapSearch'
        ));
    }

    /**
     * Otorisasi hak akses Rekap Pelapor:
     * - Superadmin memiliki akses global ke seluruh fungsi
     * - Admin HSSE & Manager HSSE hanya dapat melihat Rekap Pelapor HSSE (kecuali Superadmin)
     * - Admin Function & Manager Function hanya dapat melihat Rekap Pelapor fungsinya sendiri
     * - Role lain: tidak ada akses
     */
    private function canAccessRekapPelapor($user, string $targetFungsi): bool
    {
        // 1. Superadmin (role Superadmin atau user 'admin') memiliki akses global
        if ($user->role === 'Superadmin' || $user->username === 'admin') {
            return true;
        }

        // 2. Admin HSSE & Manager HSSE hanya dapat melihat Rekap Pelapor HSSE
        if ($user->role === 'Admin HSSE' || $user->role === 'Manager HSSE') {
            return strtolower($targetFungsi) === 'hsse';
        }

        // 3. Admin Function & Manager Function hanya dapat melihat Rekap Pelapor fungsinya sendiri
        if ($user->role === 'Admin Function' || $user->role === 'Manager Function') {
            return strtolower($user->fungsi) === strtolower($targetFungsi);
        }

        return false;
    }

    /**
     * Resolusi dan normalisasi nama fungsi dari parameter URL atau user.
     */
    private function resolveFungsi(?string $nama_fungsi, $user): string
    {
        $raw = $nama_fungsi ?? $user->fungsi;
        if (empty($raw)) {
            abort(404, 'Fungsi tidak ditemukan.');
        }

        $validFunctions = ['Operation', 'Maintenance', 'HSSE', 'Business Support'];
        $cleaned = strtolower(str_replace(['-', '_'], ' ', trim($raw)));

        foreach ($validFunctions as $vf) {
            if (strtolower($vf) === $cleaned) {
                return $vf;
            }
        }

        abort(404, 'Fungsi tidak ditemukan.');
    }

    /**
     * Bangun nama file PDF yang informatif berdasarkan fungsi dan periode.
     */
    private function buildPdfFilename(string $fungsi, string $periode, int $bulan, int $tahun): string
    {
        $slug = \Illuminate\Support\Str::slug($fungsi);

        if (empty($periode)) {
            return "rekap-pelapor-{$slug}-semua-waktu.pdf";
        }

        if ($periode === 'per_bulan') {
            $paddedBulan = str_pad($bulan, 2, '0', STR_PAD_LEFT);
            return "rekap-pelapor-{$slug}-{$tahun}-{$paddedBulan}.pdf";
        }

        $suffix = match ($periode) {
            '1_day'   => '1-hari-terakhir',
            '3_days'  => '3-hari-terakhir',
            '1_week'  => '1-minggu-terakhir',
            '1_month' => '1-bulan-terakhir',
            default   => 'semua-waktu',
        };

        return "rekap-pelapor-{$slug}-{$suffix}.pdf";
    }
}

