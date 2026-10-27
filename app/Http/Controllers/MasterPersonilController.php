<?php

namespace App\Http\Controllers;

use App\Models\MasterPersonil;
use App\Http\Requests\StoreMasterPersonilRequest;
use App\Http\Requests\UpdateMasterPersonilRequest;
use Illuminate\Http\Request;

class MasterPersonilController extends Controller
{
    /**
     * Tampilkan daftar Master Personil dengan filter dan pencarian.
     */
    public function index(Request $request)
    {
        $query = MasterPersonil::query();

        // 1. Pencarian teks (nama, nopek, jabatan)
        if ($request->filled('q')) {
            $query->search($request->q);
        }

        // 2. Filter Fungsi
        if ($request->filled('fungsi') && in_array($request->fungsi, MasterPersonil::FUNGSI_LIST)) {
            $query->byFungsi($request->fungsi);
        }

        // 3. Filter Tipe Manpower (Pekerja / TAD)
        if ($request->filled('tipe') && in_array($request->tipe, MasterPersonil::TIPE_LIST)) {
            $query->byTipe($request->tipe);
        }

        // 4. Filter Status Aktif
        if ($request->filled('status')) {
            if ($request->status === 'aktif') {
                $query->where('status_aktif', true);
            } elseif ($request->status === 'nonaktif') {
                $query->where('status_aktif', false);
            }
        }

        // Sorting default: Nama ASC
        $personils = $query->orderBy('nama', 'asc')->paginate(20)->withQueryString();

        // Summary Counters untuk KPI Cards
        $totalPersonil = MasterPersonil::count();
        $totalPekerja  = MasterPersonil::where('tipe_manpower', 'Pekerja')->count();
        $totalTad      = MasterPersonil::where('tipe_manpower', 'TAD')->count();
        $totalAktif    = MasterPersonil::where('status_aktif', true)->count();

        // Per-fungsi counter
        $fungsiCounts = [];
        foreach (MasterPersonil::FUNGSI_LIST as $fn) {
            $fungsiCounts[$fn] = MasterPersonil::where('fungsi', $fn)->where('status_aktif', true)->count();
        }

        $fungsiList = MasterPersonil::FUNGSI_LIST;
        $tipeList   = MasterPersonil::TIPE_LIST;

        return view('pages.master.personil.index', compact(
            'personils',
            'totalPersonil',
            'totalPekerja',
            'totalTad',
            'totalAktif',
            'fungsiCounts',
            'fungsiList',
            'tipeList'
        ));
    }

    /**
     * Simpan data personil baru.
     */
    public function store(StoreMasterPersonilRequest $request)
    {
        $personil = MasterPersonil::create([
            'nama'          => $request->nama,
            'nopek'         => $request->nopek,
            'fungsi'        => $request->fungsi,
            'jabatan'       => $request->jabatan,
            'tipe_manpower' => $request->tipe_manpower,
            'status_aktif'  => $request->boolean('status_aktif', true),
        ]);

        return back()->with('success', "Personil {$personil->nama} ({$personil->tipe_manpower} - {$personil->fungsi}) berhasil ditambahkan.");
    }

    /**
     * Perbarui data personil.
     */
    public function update(UpdateMasterPersonilRequest $request, MasterPersonil $personil)
    {
        $personil->update([
            'nama'          => $request->nama,
            'nopek'         => $request->nopek,
            'fungsi'        => $request->fungsi,
            'jabatan'       => $request->jabatan,
            'tipe_manpower' => $request->tipe_manpower,
            'status_aktif'  => $request->boolean('status_aktif', true),
        ]);

        return back()->with('success', "Data personil {$personil->nama} berhasil diperbarui.");
    }

    /**
     * Aktifkan atau nonaktifkan personil (Soft status toggle).
     */
    public function toggleStatus(MasterPersonil $personil)
    {
        $personil->status_aktif = !$personil->status_aktif;
        $personil->save();

        $statusLabel = $personil->status_aktif ? 'diaktifkan kembali' : 'dinonaktifkan';
        return back()->with('success', "Status personil {$personil->nama} berhasil {$statusLabel}.");
    }

    /**
     * Hapus personil permanen.
     *
     * Kebijakan Integritas Data:
     * Menolak penghapusan permanen jika personil memiliki riwayat temuan observasi di SIPEKA.
     * Admin diarahkan untuk menonaktifkan status personil.
     */
    public function destroy(MasterPersonil $personil)
    {
        if ($personil->hasReportHistory()) {
            $count = $personil->reportsCount();
            return back()->with('error', "Personil '{$personil->nama}' tidak dapat dihapus permanen karena tercatat memiliki {$count} riwayat laporan observasi di data SIPEKA. Silakan ubah statusnya menjadi 'Non-Aktif' untuk menjaga integritas data.");
        }

        if ($personil->sipekaMappings()->exists()) {
            return back()->with('error', "Personil '{$personil->nama}' tidak dapat dihapus karena menjadi target Mapping Pelapor SIPEKA. Hapus mapping terkait terlebih dahulu atau ubah statusnya menjadi 'Non-Aktif'.");
        }

        $nama = $personil->nama;
        $personil->delete();

        return back()->with('success', "Personil {$nama} berhasil dihapus dari sistem.");
    }
}
