<?php

namespace App\Http\Controllers;

use App\Services\ImportService;
use Illuminate\Http\Request;

class SipekaImportController extends Controller
{
    public function __construct(
        private readonly ImportService $importService
    ) {}

    /**
     * Proses upload dan import file Excel SIPEKA.
     *
     * Business Rule #6:
     *   Import TIDAK menimpa no_notifikasi_sap dan keterangan_tindak_lanjut.
     *   Dijamin oleh SipekaFindingsImport::upsertColumns().
     *
     * Business Rule #12:
     *   Setiap import disimpan ke import_logs (jumlah insert, update, skip, error, durasi).
     */
    public function upload(Request $request)
    {
        $request->validate([
            'sipeka_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'sipeka_file.required' => 'File SIPEKA wajib dipilih.',
            'sipeka_file.mimes'    => 'File harus berformat .xlsx, .xls, atau .csv.',
            'sipeka_file.max'      => 'Ukuran file maksimal 10MB.',
        ]);

        try {
            $log = $this->importService->import($request->file('sipeka_file'));

            $message = sprintf(
                'Sinkronisasi berhasil! %d data baru ditambahkan, %d data diperbarui, %d data dilewati.',
                $log->jumlah_insert,
                $log->jumlah_update,
                $log->jumlah_skip
            );

            if ($log->jumlah_error > 0) {
                $message .= " ({$log->jumlah_error} baris error)";
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengunggah file: ' . $e->getMessage());
        }
    }
}
