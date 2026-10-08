<?php

namespace App\Http\Controllers;

use App\Models\MasterPersonil;
use App\Services\UnreportedPersonilService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UnreportedPersonilController extends Controller
{
    public function __construct(
        protected UnreportedPersonilService $unreportedService
    ) {}

    /**
     * Endpoint API JSON untuk mengambil daftar personil yang BELUM PERNAH melapor.
     * Status dihitung dari SELURUH riwayat data SIPEKA (tidak dibatasi bulan/tahun).
     * Digunakan oleh Modal AJAX pada Dashboard Global dan Dashboard Fungsi.
     */
    public function data(Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'fungsi'   => ['required', 'string', 'in:' . implode(',', MasterPersonil::FUNGSI_LIST)],
            'q'        => ['nullable', 'string', 'max:100'],
            'tipe'     => ['nullable', 'string', 'in:Pekerja,TAD'],
            'page'     => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:100'],
        ]);

        $fungsi  = $request->fungsi;
        $search  = $request->filled('q') ? trim($request->q) : null;
        $tipe    = $request->filled('tipe') ? $request->tipe : null;
        $perPage = $request->integer('per_page', 10);
        $page    = $request->integer('page', 1);

        // 2. Otorisasi role: Admin Function & Manager Function hanya boleh melihat fungsi mereka sendiri
        $user = auth()->user();
        if ($user && in_array($user->role, ['Admin Function', 'Manager Function'])) {
            if ($user->fungsi !== $fungsi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki hak akses untuk melihat data fungsi lain.',
                ], 403);
            }
        }

        // 3. Ambil data dari service
        $result    = $this->unreportedService->getUnreportedPersonils($fungsi, $search, $tipe, $perPage, $page);
        $paginator = $result['paginator'];

        // 4. Format data untuk antarmuka
        $items = collect($paginator->items())->map(function (MasterPersonil $p) {
            return [
                'id'             => $p->id,
                'id_personil'    => $p->id_personil,
                'nama'           => $p->nama,
                'nopek'          => $p->nopek,
                'is_tad'         => $p->tipe_manpower === 'TAD',
                // NOPEK asli bila ada; jika tidak, tampilkan ID Personil internal (bukan NOPEK resmi)
                'display_id'     => $p->nopek ?: $p->id_personil,
                'fungsi'         => $p->fungsi,
                'jabatan'        => $p->jabatan ?: '—',
                'tipe_manpower'  => $p->tipe_manpower,
                'status_aktif'   => $p->status_aktif,
                'status_label'   => 'Belum Melapor',
            ];
        });

        return response()->json([
            'success'    => true,
            'meta'       => $result['meta'],
            'summary'    => $result['summary'],
            'data'       => $items,
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'from'         => $paginator->firstItem() ?? 0,
                'to'           => $paginator->lastItem() ?? 0,
            ],
        ]);
    }

    /**
     * Export daftar personil belum pernah melapor ke CSV (kompatibel Microsoft Excel).
     * Hanya mengekspor data sesuai search/filter yang sedang ditampilkan.
     */
    public function export(Request $request): StreamedResponse
    {
        $request->validate([
            'fungsi' => ['required', 'string', 'in:' . implode(',', MasterPersonil::FUNGSI_LIST)],
            'q'      => ['nullable', 'string', 'max:100'],
            'tipe'   => ['nullable', 'string', 'in:Pekerja,TAD'],
        ]);

        $fungsi = $request->fungsi;
        $search = $request->filled('q') ? trim($request->q) : null;
        $tipe   = $request->filled('tipe') ? $request->tipe : null;

        // Otorisasi role
        $user = auth()->user();
        if ($user && in_array($user->role, ['Admin Function', 'Manager Function'])) {
            if ($user->fungsi !== $fungsi) {
                abort(403, 'Akses fungsi ditolak.');
            }
        }

        $records  = $this->unreportedService->getUnreportedForExport($fungsi, $search, $tipe);
        $slug     = preg_replace('/[^A-Za-z0-9]+/', '_', $fungsi);
        $filename = 'Daftar_Belum_Pernah_Melapor_' . $slug . '_' . now()->format('Ymd') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        // Mencegah formula injection saat CSV dibuka di Excel (nilai teks dari sumber luar)
        $safe = fn ($v) => (is_string($v) && $v !== '' && in_array($v[0], ['=', '+', '-', '@'], true)) ? "'" . $v : $v;

        return response()->stream(function () use ($records, $safe) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM agar Excel di Windows membaca karakter dengan encoding benar
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'No',
                'Nama',
                'NOPEK',
                'ID Personil',
                'Jabatan',
                'Fungsi',
                'Tipe Manpower',
                'Status',
            ]);

            $no = 1;
            foreach ($records as $p) {
                fputcsv($handle, [
                    $no++,
                    $safe($p->nama),
                    $p->nopek ?: '-',
                    $p->id_personil,
                    $safe($p->jabatan ?: '-'),
                    $p->fungsi,
                    $p->tipe_manpower,
                    'Belum Pernah Melapor',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
