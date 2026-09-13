<?php

namespace App\Http\Controllers;

use App\Models\MasterFunctionMapping;
use App\Http\Requests\StoreMasterFunctionMappingRequest;
use App\Http\Requests\UpdateMasterFunctionMappingRequest;

class MasterFunctionMappingController extends Controller
{
    /**
     * Tampilkan halaman Master Mapping Fungsi.
     *
     * Mengirim ke view:
     *   $mappings        — seluruh mapping, diurutkan fungsi_dashboard lalu fungsi_sipeka
     *   $unmappedFungsi  — nilai FUNGSI dari sipeka_findings yang belum dipetakan
     *   $fungsiList      — 4 fungsi dashboard untuk dropdown
     */
    public function index()
    {
        $mappings = MasterFunctionMapping::query()
            ->orderBy('fungsi_dashboard')
            ->orderBy('fungsi_sipeka')
            ->get();

        $unmappedFungsi       = MasterFunctionMapping::unmappedFungsi();
        $fungsiList           = MasterFunctionMapping::DASHBOARD_FUNGSI_LIST;
        $kelompokKhususList   = MasterFunctionMapping::KELOMPOK_KHUSUS_LIST;

        return view('pages.master.function-mapping.index', compact(
            'mappings',
            'unmappedFungsi',
            'fungsiList',
            'kelompokKhususList'
        ));
    }

    /**
     * Simpan mapping baru.
     * Validasi dilakukan via StoreMasterFunctionMappingRequest.
     */
    public function store(StoreMasterFunctionMappingRequest $request)
    {
        MasterFunctionMapping::create([
            'fungsi_sipeka'    => $request->fungsi_sipeka,
            'fungsi_dashboard' => $request->fungsi_dashboard ?: null,
            'kelompok_khusus'  => $request->kelompok_khusus ?: null,
        ]);

        return back()->with('success', 'Mapping fungsi berhasil ditambahkan.');
    }

    /**
     * Update mapping existing.
     * Validasi via UpdateMasterFunctionMappingRequest.
     * Record yang sedang diedit dikecualikan dari pengecekan unique.
     */
    public function update(UpdateMasterFunctionMappingRequest $request, MasterFunctionMapping $mapping)
    {
        $mapping->fungsi_sipeka    = $request->fungsi_sipeka;
        $mapping->fungsi_dashboard = $request->fungsi_dashboard ?: null;
        $mapping->kelompok_khusus  = $request->kelompok_khusus ?: null;
        $mapping->save();

        return back()->with('success', 'Mapping fungsi berhasil diperbarui.');
    }

    /**
     * Hapus mapping.
     *
     * Menghapus mapping TIDAK menghapus data temuan SIPEKA.
     * sipeka_findings tetap utuh.
     */
    public function destroy(MasterFunctionMapping $mapping)
    {
        $label = "{$mapping->fungsi_sipeka} → {$mapping->fungsi_dashboard}";
        $mapping->delete();

        return back()->with('success', "Mapping fungsi \"{$label}\" berhasil dihapus.");
    }
}
