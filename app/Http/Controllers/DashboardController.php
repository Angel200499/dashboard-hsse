<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SipekaFinding;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user->role === 'Admin Function' || $user->role === 'Manager Function') {
            return redirect()->route('dashboard.fungsi', $user->fungsi);
        }

        $findings = SipekaFinding::all();

        // 1. KPIs
        $kpi = [
            'total' => $findings->count(),
            'open' => $findings->filter(function($item) {
                return strtolower($item->data_sipeka['status'] ?? '') === 'open';
            })->count(),
            'closed' => $findings->filter(function($item) {
                return strtolower($item->data_sipeka['status'] ?? '') === 'closed';
            })->count(),
        ];

        // Helper function for counts
        $countBy = function($findings, $key) {
            return $findings->countBy(function ($item) use ($key) {
                return $item->data_sipeka[$key] ?? 'Unknown';
            })->sortDesc();
        };

        // 1. Jumlah Pelaporan per Fungsi (Pie)
        $chartFungsi = $countBy($findings, 'fungsi');

        // 2. Reporting Rate per Fungsi (Horizontal Bar) - Just using raw counts for now as rate placeholder
        $chartReportingRate = $chartFungsi; 

        // 3. Kategori PEKA (Pie)
        $chartKategori = $countBy($findings, 'kategori');

        // 4. Keterlibatan Observasi (Stacked Bar) - using userstatus or pelapor count as proxy
        // Placeholder: total findings per fungsi
        $chartKeterlibatan = $chartFungsi; 

        // 5. Rekap Persentase Temuan per Fungsi (Stacked Bar)
        $chartPersentaseFungsi = $chartFungsi; 

        // 6. Rekap Persentase Penindak Lanjut (Donut)
        $sapFilled = $findings->whereNotNull('no_notifikasi_sap')
                              ->filter(fn($i) => trim($i->no_notifikasi_sap) !== '')->count();
        $sapEmpty = $findings->count() - $sapFilled;
        $chartTindakLanjut = collect(['Memiliki No. SAP' => $sapFilled, 'Belum Memiliki' => $sapEmpty]);

        // 7. Unsafe Action Category (Horizontal Bar)
        $chartUnsafeAction = $countBy($findings, 'unsafe_action')->filter(fn($v, $k) => $k !== 'Unknown' && trim($k) !== '');

        // 8. Unsafe Condition Category (Horizontal Bar) - noting the typo unsafe_conditon
        $chartUnsafeCondition = $countBy($findings, 'unsafe_conditon')->filter(fn($v, $k) => $k !== 'Unknown' && trim($k) !== '');

        // Pass to view
        $charts = [
            'fungsi' => ['labels' => $chartFungsi->keys(), 'data' => $chartFungsi->values()],
            'reporting' => ['labels' => $chartReportingRate->keys(), 'data' => $chartReportingRate->values()],
            'kategori' => ['labels' => $chartKategori->keys(), 'data' => $chartKategori->values()],
            'tindak_lanjut' => ['labels' => $chartTindakLanjut->keys(), 'data' => $chartTindakLanjut->values()],
            'unsafe_action' => ['labels' => $chartUnsafeAction->keys(), 'data' => $chartUnsafeAction->values()],
            'unsafe_condition' => ['labels' => $chartUnsafeCondition->keys(), 'data' => $chartUnsafeCondition->values()],
        ];

        return view('pages.dashboard', compact('kpi', 'charts'));
    }
}
