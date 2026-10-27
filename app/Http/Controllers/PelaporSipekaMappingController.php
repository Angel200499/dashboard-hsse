<?php

namespace App\Http\Controllers;

use App\Models\MasterPersonil;
use App\Models\PelaporSipekaMapping;
use App\Services\PelaporResolverService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

/**
 * Master Data → Mapping Pelapor SIPEKA (khusus Admin HSSE, dilindungi middleware di routes).
 *
 * Menghubungkan nama pelapor SIPEKA ke Master Personil secara MANUAL.
 * Hanya menulis ke pelapor_sipeka_mappings. TIDAK mengubah sipeka_findings,
 * master_personils, maupun master_manpowers, dan tidak memengaruhi grafik Keterlibatan.
 */
class PelaporSipekaMappingController extends Controller
{
    private const PER_PAGE = 20;

    public function __construct(
        protected PelaporResolverService $resolver
    ) {}

    // -----------------------------------------------------------------
    // Halaman utama (2 tab: Belum Dipetakan / Sudah Dipetakan)
    // -----------------------------------------------------------------
    public function index(Request $request)
    {
        $request->validate([
            'status' => ['nullable', Rule::in(['belum', 'sudah'])],
            'fungsi' => ['nullable', Rule::in(MasterPersonil::FUNGSI_LIST)],
            'q'      => ['nullable', 'string', 'max:100'],
            'page'   => ['nullable', 'integer', 'min:1'],
        ]);

        $status = $request->input('status', 'belum');
        $fungsi = $request->input('fungsi');
        $needle = PelaporSipekaMapping::normalize($request->input('q'));

        $stats      = $this->resolver->reporterStats();
        $resolution = $this->resolver->resolve();

        // ---- Daftar BELUM dipetakan (tanpa mapping manual & tanpa exact match) ----
        $unmapped = collect($resolution['unresolved'])->values();

        // ---- Daftar SUDAH dipetakan (mapping manual) ----
        $mappings = PelaporSipekaMapping::with('personil')->get();

        $countBelum = $unmapped->count();
        $countSudah = $mappings->count();

        if ($status === 'sudah') {
            $rows = $mappings
                ->filter(fn ($m) => $m->personil !== null)
                ->map(function (PelaporSipekaMapping $m) use ($stats) {
                    $p = $m->personil;

                    return (object) [
                        'id'           => $m->id,
                        'nama_sipeka'  => $m->nama_sipeka,
                        'jumlah'       => $stats[$m->nama_sipeka_normalized]['count'] ?? 0,
                        'personil_id'  => $p->id,
                        'personil'     => $p->nama,
                        'nopek'        => $p->nopek,
                        'id_personil'  => $p->id_personil,
                        'fungsi'       => $p->fungsi,
                        'tipe'         => $p->tipe_manpower,
                        'aktif'        => $p->status_aktif,
                    ];
                })
                ->filter(function ($r) use ($fungsi, $needle) {
                    if ($fungsi && $r->fungsi !== $fungsi) {
                        return false;
                    }
                    if ($needle === '') {
                        return true;
                    }
                    foreach ([$r->nama_sipeka, $r->personil, $r->nopek] as $f) {
                        if ($f !== null && str_contains(mb_strtolower((string) $f, 'UTF-8'), $needle)) {
                            return true;
                        }
                    }

                    return false;
                })
                ->sortBy(fn ($r) => PelaporSipekaMapping::normalize($r->nama_sipeka))
                ->values();
        } else {
            $rows = $unmapped
                ->filter(function ($s) use ($fungsi, $needle) {
                    if ($fungsi && $s['fungsi'] !== $fungsi) {
                        return false;
                    }

                    return $needle === '' || str_contains($s['key'], $needle);
                })
                ->sortByDesc('count')
                ->values();
        }

        $rows = $this->paginate($rows, $request);

        return view('pages.master.pelapor-mapping.index', [
            'rows'        => $rows,
            'status'      => $status,
            'countBelum'  => $countBelum,
            'countSudah'  => $countSudah,
            'fungsiList'  => MasterPersonil::FUNGSI_LIST,
            'totalSipeka' => count($stats),
            'autoExact'   => $resolution['exact_count'],
        ]);
    }

    // -----------------------------------------------------------------
    // Pencarian Master Personil untuk modal mapping (JSON)
    // -----------------------------------------------------------------
    public function searchPersonil(Request $request)
    {
        $request->validate([
            'q'      => ['nullable', 'string', 'max:100'],
            'fungsi' => ['nullable', Rule::in(MasterPersonil::FUNGSI_LIST)],
            'semua'  => ['nullable', 'boolean'],
        ]);

        $fungsi = $request->input('fungsi');
        $semua  = $request->boolean('semua');
        $q      = trim((string) $request->input('q', ''));

        $query = MasterPersonil::query()->active();

        // Prioritaskan fungsi pelapor; fungsi lain hanya jika diminta eksplisit.
        if ($fungsi && !$semua) {
            $query->byFungsi($fungsi);
        }
        if ($q !== '') {
            $query->search($q);
        }
        if ($fungsi && $semua) {
            $query->orderByRaw('CASE WHEN fungsi = ? THEN 0 ELSE 1 END', [$fungsi]);
        }

        $items = $query->orderBy('nama')->limit(20)->get()->map(fn (MasterPersonil $p) => [
            'id'          => $p->id,
            'nama'        => $p->nama,
            'nopek'       => $p->nopek,
            'id_personil' => $p->id_personil,
            'fungsi'      => $p->fungsi,
            'tipe'        => $p->tipe_manpower,
            'jabatan'     => $p->jabatan,
        ]);

        return response()->json(['success' => true, 'data' => $items]);
    }

    // -----------------------------------------------------------------
    // Simpan mapping baru
    // -----------------------------------------------------------------
    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_sipeka'        => ['required', 'string', 'max:255'],
            'master_personil_id' => ['required', 'integer', 'exists:master_personils,id'],
        ]);

        $normalized = PelaporSipekaMapping::normalize($data['nama_sipeka']);
        $stats      = $this->resolver->reporterStats();

        // Hanya nama yang benar-benar ada di SIPEKA yang boleh dipetakan
        if ($normalized === '' || !isset($stats[$normalized])) {
            return back()->with('error', 'Nama pelapor tersebut tidak ditemukan pada data SIPEKA.');
        }

        if (PelaporSipekaMapping::where('nama_sipeka_normalized', $normalized)->exists()) {
            return back()->with('error', "Nama SIPEKA \"{$stats[$normalized]['nama']}\" sudah dipetakan. Gunakan tab Sudah Dipetakan → Edit untuk mengubahnya.");
        }

        $personil = MasterPersonil::findOrFail($data['master_personil_id']);

        PelaporSipekaMapping::create([
            'nama_sipeka'            => $stats[$normalized]['nama'],
            'nama_sipeka_normalized' => $normalized,
            'master_personil_id'     => $personil->id,
            'created_by'             => auth()->id(),
            'updated_by'             => auth()->id(),
        ]);

        return back()->with('success', "Mapping \"{$stats[$normalized]['nama']}\" → {$personil->nama} berhasil disimpan.");
    }

    // -----------------------------------------------------------------
    // Ubah target personil mapping (nama SIPEKA tidak berubah)
    // -----------------------------------------------------------------
    public function update(Request $request, PelaporSipekaMapping $mapping)
    {
        $data = $request->validate([
            'master_personil_id' => ['required', 'integer', 'exists:master_personils,id'],
        ]);

        $personil = MasterPersonil::findOrFail($data['master_personil_id']);

        $mapping->update([
            'master_personil_id' => $personil->id,
            'updated_by'         => auth()->id(),
        ]);

        return back()->with('success', "Mapping \"{$mapping->nama_sipeka}\" diperbarui → {$personil->nama}.");
    }

    // -----------------------------------------------------------------
    // Hapus mapping (tidak menyentuh data SIPEKA maupun Master Personil)
    // -----------------------------------------------------------------
    public function destroy(PelaporSipekaMapping $mapping)
    {
        $nama = $mapping->nama_sipeka;
        $mapping->delete();

        return back()->with('success', "Mapping \"{$nama}\" berhasil dihapus.");
    }

    // -----------------------------------------------------------------
    protected function paginate(Collection $rows, Request $request): LengthAwarePaginator
    {
        $page = max(1, (int) $request->input('page', 1));

        return new LengthAwarePaginator(
            $rows->forPage($page, self::PER_PAGE)->values(),
            $rows->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }
}
