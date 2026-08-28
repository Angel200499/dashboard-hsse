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
     * Mendukung filter berdasarkan tahun.
     */
    public function index(Request $request)
    {
        $query = MasterManpower::query()->orderBy('tahun', 'desc')->orderBy('fungsi');

        // Filter tahun (opsional)
        if ($request->filled('tahun') && preg_match('/^\d{4}$/', $request->tahun)) {
            $query->byTahun((int) $request->tahun);
        }

        $manpowers   = $query->get();
        $availYears  = MasterManpower::availableYears();
        $selectedYear = $request->filled('tahun') ? (int) $request->tahun : null;
        $fungsiList  = MasterManpower::FUNGSI_LIST;

        return view('pages.master.manpower.index', compact(
            'manpowers',
            'availYears',
            'selectedYear',
            'fungsiList'
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
            'jumlah_manpower'  => $request->jumlah_manpower,
        ]);

        return back()->with('success', 'Data manpower berhasil ditambahkan.');
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
        $manpower->jumlah_manpower = $request->jumlah_manpower;
        $manpower->save();

        return back()->with('success', 'Data manpower berhasil diperbarui.');
    }

    /**
     * Hapus data manpower.
     */
    public function destroy(MasterManpower $manpower)
    {
        $label = "{$manpower->fungsi} tahun {$manpower->tahun}";
        $manpower->delete();

        return back()->with('success', "Data manpower {$label} berhasil dihapus.");
    }
}
