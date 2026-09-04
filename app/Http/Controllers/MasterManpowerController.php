<?php

namespace App\Http\Controllers;

use App\Models\MasterManpower;
use App\Http\Requests\StoreMasterManpowerRequest;
use App\Http\Requests\UpdateMasterManpowerRequest;
use Illuminate\Http\Request;

class MasterManpowerController extends Controller
{
    /**
     * Tampilkan daftar Master Manpower.
     * Mendukung filter berdasarkan tahun dan bulan.
     */
    public function index(Request $request)
    {
        $query = MasterManpower::query()
            ->orderBy('tahun', 'desc')
            ->orderBy('bulan', 'asc')
            ->orderBy('fungsi');

        // Filter tahun (opsional)
        if ($request->filled('tahun') && preg_match('/^\d{4}$/', $request->tahun)) {
            $query->byTahun((int) $request->tahun);
        }

        // Filter bulan (opsional, hanya berlaku jika tahun juga dipilih)
        if ($request->filled('bulan') && is_numeric($request->bulan)
            && (int) $request->bulan >= 1 && (int) $request->bulan <= 12) {
            $query->byBulan((int) $request->bulan);
        }

        $manpowers    = $query->get();
        $availYears   = MasterManpower::availableYears();
        $selectedYear = $request->filled('tahun') ? (int) $request->tahun : null;
        $selectedMonth = ($request->filled('bulan') && is_numeric($request->bulan))
            ? (int) $request->bulan
            : null;
        $fungsiList   = MasterManpower::FUNGSI_LIST;
        $bulanLabels  = MasterManpower::BULAN_LABELS;

        return view('pages.master.manpower.index', compact(
            'manpowers',
            'availYears',
            'selectedYear',
            'selectedMonth',
            'fungsiList',
            'bulanLabels'
        ));
    }

    /**
     * Simpan data manpower baru.
     * Validasi via StoreMasterManpowerRequest.
     */
    public function store(StoreMasterManpowerRequest $request)
    {
        MasterManpower::create([
            'fungsi'           => $request->fungsi,
            'tahun'            => $request->tahun,
            'bulan'            => $request->bulan,
            'jumlah_manpower'  => $request->jumlah_manpower,
        ]);

        $namaBulan = MasterManpower::namaBulan((int) $request->bulan);
        return back()->with('success', "Data manpower {$request->fungsi} {$namaBulan} {$request->tahun} berhasil ditambahkan.");
    }

    /**
     * Update data manpower.
     * Validasi via UpdateMasterManpowerRequest.
     * Record yang sedang diedit dikecualikan dari pengecekan unique.
     */
    public function update(UpdateMasterManpowerRequest $request, MasterManpower $manpower)
    {
        $manpower->fungsi          = $request->fungsi;
        $manpower->tahun           = $request->tahun;
        $manpower->bulan           = $request->bulan;
        $manpower->jumlah_manpower = $request->jumlah_manpower;
        $manpower->save();

        $namaBulan = MasterManpower::namaBulan((int) $request->bulan);
        return back()->with('success', "Data manpower {$request->fungsi} {$namaBulan} {$request->tahun} berhasil diperbarui.");
    }

    /**
     * Hapus data manpower.
     */
    public function destroy(MasterManpower $manpower)
    {
        $namaBulan = MasterManpower::namaBulan($manpower->bulan);
        $label = "{$manpower->fungsi} {$namaBulan} {$manpower->tahun}";
        $manpower->delete();

        return back()->with('success', "Data manpower {$label} berhasil dihapus.");
    }
}
