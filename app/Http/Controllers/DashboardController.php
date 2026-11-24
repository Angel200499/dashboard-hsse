<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SipekaFinding;
use App\Services\DashboardChartService;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardChartService $chartService
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();

        // Redirect Function roles ke Dashboard Fungsi mereka
        if ($user->role === 'Admin Function' || $user->role === 'Manager Function') {
            return redirect()->route('dashboard.fungsi', $user->fungsi);
        }

        // Validasi filter tahun (4 digit, angka saja)
        $tahunRaw = $request->get('year');
        $tahun    = ($tahunRaw && preg_match('/^\d{4}$/', $tahunRaw)) ? (int) $tahunRaw : null;

        // Validasi filter bulan (1–12) — hanya aktif jika tahun juga dipilih
        $bulanRaw = $request->get('month');
        $bulan    = ($tahun && $bulanRaw && is_numeric($bulanRaw)
                     && (int) $bulanRaw >= 1 && (int) $bulanRaw <= 12)
                    ? (int) $bulanRaw
                    : null;

        // -----------------------------------------------------------------
        // VALIDASI PERIODE REPORTING RATE
        // Ambil max bulan SIPEKA aktual untuk tahun yang dipilih.
        // Jika user mengirim bulan melebihi max (mis. via URL bypass),
        // clamp ke max bulan yang valid. Ini mencegah kalkulasi Reporting Rate
        // dengan data yang belum tersedia.
        // -----------------------------------------------------------------
        $maxBulanSipeka = $tahun ? SipekaFinding::maxBulanTahun($tahun) : null;

        if ($bulan !== null && $maxBulanSipeka !== null && $bulan > $maxBulanSipeka) {
            // Bulan yang diminta melebihi data aktual — clamp ke max yang valid
            $bulan = $maxBulanSipeka;
        }

        // -----------------------------------------------------------------
        // Ambil tahun data SIPEKA terakhir secara global & daftar tahun opsi
        // -----------------------------------------------------------------
        $latestDataYear = SipekaFinding::maxTahun();
        $tahunList      = SipekaFinding::getTahunList();

        // Filter Tahun mandiri untuk Rekap PEKA
        // Default ke tahun data terbaru agar chart langsung tampil saat pertama buka
        $pekaYearRaw = $request->get('peka_year');
        $pekaYear    = ($pekaYearRaw && preg_match('/^\d{4}$/', $pekaYearRaw))
                       ? (int) $pekaYearRaw
                       : ($tahun ?? $latestDataYear ?? (int) now()->year);

        // -----------------------------------------------------------------
        // FILTER MANDIRI & DEFAULT OTOMATIS: KETERLIBATAN OBSERVASI (CARD 4) & REPORTING RATE (CARD 2)
        // -----------------------------------------------------------------

        // ko_year: jika ada ko_year, gunakan. Fallback ke tahun filter atau data aktual terbaru.
        $koYearRaw = $request->get('ko_year');
        if ($koYearRaw !== null && preg_match('/^\d{4}$/', $koYearRaw)) {
            $koYear = (int) $koYearRaw;
        } else {
            $koYear = $tahun ?? $latestDataYear;
        }

        $koMaxBulan = $koYear ? SipekaFinding::maxBulanTahun($koYear) : null;

        if ($request->has('ko_month')) {
            $koMonthRaw = $request->get('ko_month');
            if ($koMonthRaw !== null && $koMonthRaw !== '' && is_numeric($koMonthRaw) && (int) $koMonthRaw >= 1 && (int) $koMonthRaw <= 12) {
                $koMonth = (int) $koMonthRaw;
            } else {
                $koMonth = null; // User eksplisit memilih "Semua Bulan"
            }
        } elseif ($request->has('month')) {
            $koMonth = $bulan;
        } else {
            $koMonth = $koMaxBulan;
        }

        // rr_year: jika ada rr_year, gunakan. Fallback ke tahun filter atau data aktual terbaru.
        $rrYearRaw = $request->get('rr_year');
        if ($rrYearRaw !== null && preg_match('/^\d{4}$/', $rrYearRaw)) {
            $rrYear = (int) $rrYearRaw;
        } else {
            $rrYear = $tahun ?? $latestDataYear;
        }

        $rrMaxBulan = $rrYear ? SipekaFinding::maxBulanTahun($rrYear) : null;

        if ($request->has('rr_month')) {
            $rrMonthRaw = $request->get('rr_month');
            if ($rrMonthRaw !== null && $rrMonthRaw !== '' && is_numeric($rrMonthRaw) && (int) $rrMonthRaw >= 1 && (int) $rrMonthRaw <= 12) {
                $rrMonth = (int) $rrMonthRaw;
            } else {
                $rrMonth = null; // User eksplisit memilih "Semua Bulan"
            }
        } elseif ($request->has('month')) {
            $rrMonth = $bulan;
        } else {
            $rrMonth = $rrMaxBulan;
        }

        // Status ketersediaan data Reporting Rate Card 2
        $rrStatus = 'ok';
        if ($request->has('rr_month') && ($request->get('rr_month') === '' || $request->get('rr_month') === null)) {
            $rrStatus = 'all_months';
        } elseif ($rrMaxBulan === null) {
            $rrStatus = 'no_sipeka';
        } elseif ($rrMonth === null) {
            $rrStatus = 'no_sipeka';
        } elseif ($rrMaxBulan !== null && $rrMonth > $rrMaxBulan) {
            $rrStatus = 'no_sipeka';
        }

        // -----------------------------------------------------------------
        // KPI — dihitung via SQL CASE WHEN (computed monitoring status)
        // Difilter by tahun jika ada.
        // KPI TIDAK dipengaruhi filter bulan — hanya tahun.
        // -----------------------------------------------------------------
        $baseKpi = SipekaFinding::query();
        if ($tahun) {
            $baseKpi->whereRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE ?",
                ["%{$tahun}%"]
            );
        }

        $total = (clone $baseKpi)->count();

        $closed = (clone $baseKpi)->whereRaw(
            "LOWER(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.status'))) = 'closed'"
        )->count();

        $inProgress = (clone $baseKpi)->whereRaw(
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
        // Charts — DashboardChartService, difilter by tahun + bulan
        // $fungsi = null → Global Dashboard (semua fungsi)
        // -----------------------------------------------------------------
        $charts = $this->chartService->getCharts(
            null,
            $tahun,
            $bulan,
            $pekaYear,
            $koYear,
            $koMonth,
            $rrYear,
            $rrMonth,
            true
        );

        // Filter yang dipilih dikirim ke view untuk UI
        $selectedYear     = $tahun;
        $selectedMonth    = $bulan;
        $selectedPekaYear = $pekaYear;

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'          => true,
                'kpi'              => $kpi,
                'charts'           => $charts,
                'selectedYear'     => $selectedYear,
                'selectedMonth'    => $selectedMonth,
                'selectedPekaYear' => $selectedPekaYear,
                'maxBulanSipeka'   => $maxBulanSipeka,
                'latestDataYear'   => $latestDataYear,
                'tahunList'        => $tahunList,
                'koYear'           => $koYear,
                'koMonth'          => $koMonth,
                'koMaxBulan'       => $koMaxBulan,
                'rrYear'           => $rrYear,
                'rrMonth'          => $rrMonth,
                'rrStatus'         => $rrStatus,
                'rrMaxBulan'       => $rrMaxBulan,
            ]);
        }

        return view('pages.dashboard', compact(
            'kpi', 'charts',
            'selectedYear', 'selectedMonth', 'selectedPekaYear',
            'maxBulanSipeka',
            'latestDataYear',
            'tahunList',
            'koYear', 'koMonth', 'koMaxBulan',
            'rrYear', 'rrMonth', 'rrStatus', 'rrMaxBulan'
        ));
    }

    /**
     * Export Executive Summary PDF (2 Pages) matching Mentor Reference.
     */
    public function exportExecutiveSummaryPdf(Request $request, \App\Services\ExecutiveSummaryPdfService $pdfService)
    {
        $tahunRaw = $request->get('year');
        $tahun    = ($tahunRaw && preg_match('/^\d{4}$/', $tahunRaw)) ? (int) $tahunRaw : (int) now()->year;

        $bulanRaw = $request->get('month');
        $bulan    = ($bulanRaw && is_numeric($bulanRaw)) ? (int) $bulanRaw : null;

        $pdf = $pdfService->generate($tahun, $bulan);

        $filename = "PEKA_Report_ytd_{$tahun}" . ($bulan ? "_{$bulan}" : "") . ".pdf";
        return $pdf->download($filename);
    }
}
