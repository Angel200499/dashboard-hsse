<?php

namespace App\Services;

use App\Models\MasterManpower;
use App\Models\MasterPersonil;
use App\Models\SipekaFinding;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Daftar personil aktif yang BELUM PERNAH tercatat sebagai pelapor SIPEKA.
 *
 * Aturan bisnis final:
 *  - Seorang personil "sudah pernah melapor" jika namanya muncul minimal 1x sebagai
 *    `pelapor` pada SELURUH data SIPEKA yang tersedia (tanpa batas tahun/bulan/YTD).
 *  - Seorang personil "belum pernah melapor" jika namanya tidak pernah muncul.
 *  - Matching memakai nama ternormalisasi (trim + spasi ganda dirapikan + lowercase).
 *    Tidak ada fuzzy matching ("Aji B. Kurniawan" ≠ "Aji Bayu Kurniawan").
 *  - Urutan resolusi pelapor (lihat PelaporResolverService): mapping manual → exact
 *    normalized match → belum terpetakan. Mapping hanya memengaruhi daftar ini,
 *    TIDAK memengaruhi grafik Keterlibatan.
 *
 * Performa: seluruh nama pelapor SIPEKA diambil dalam SATU query (DISTINCT) dan dibuat
 * menjadi lookup set di memory — tidak ada N personil → N query.
 */
class UnreportedPersonilService
{
    /** Nilai scope khusus: seluruh 4 fungsi utama (Dashboard Global). GM TIDAK termasuk. */
    public const SCOPE_ALL = 'all';

    public function __construct(
        protected PelaporResolverService $resolver
    ) {}

    /**
     * Normalisasi nama untuk pencocokan aman:
     * trim, rapikan whitespace berulang menjadi satu spasi, lowercase.
     */
    public static function normalizeName(?string $name): string
    {
        $name = preg_replace('/\s+/u', ' ', (string) $name) ?? '';

        return mb_strtolower(trim($name), 'UTF-8');
    }

    /**
     * Seluruh nama pelapor unik dari SELURUH data SIPEKA (tanpa filter periode/fungsi).
     *
     * @return array<string, string>  [nama_ternormalisasi => nama_asli (untuk ditampilkan)]
     */
    public function getReporterLookup(): array
    {
        $rawNames = SipekaFinding::query()
            ->selectRaw("DISTINCT NULLIF(TRIM(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor'))), '') as pelapor")
            ->pluck('pelapor')
            ->filter();

        $lookup = [];
        foreach ($rawNames as $raw) {
            $key = self::normalizeName($raw);
            if ($key !== '' && !isset($lookup[$key])) {
                $lookup[$key] = preg_replace('/\s+/u', ' ', trim((string) $raw));
            }
        }

        return $lookup;
    }

    /**
     * Personil aktif pada fungsi tertentu yang BELUM PERNAH melapor + ringkasan.
     *
     * @return array{personils: Collection<int, MasterPersonil>, summary: array}
     */
    protected function resolve(string $fungsi): array
    {
        // Resolusi pelapor: Manual Mapping → exact normalized match → belum terpetakan
        $resolution = $this->resolver->resolve();
        $reportedIds = $resolution['reported_ids'];

        $query = MasterPersonil::active();
        if ($fungsi === self::SCOPE_ALL) {
            $query->whereIn('fungsi', MasterPersonil::FUNGSI_LIST); // 4 fungsi utama; GM bukan bagian
        } else {
            $query->byFungsi($fungsi);
        }

        $active = $query->get()
            ->sortBy(fn (MasterPersonil $p) => self::normalizeName($p->nama))
            ->values();

        $unreported = $active
            ->reject(fn (MasterPersonil $p) => isset($reportedIds[$p->id]))
            ->values();

        return [
            'unreported' => $unreported,
            'summary'    => [
                'total_active'     => $active->count(),
                'total_unreported' => $unreported->count(),
                'total_reported'   => $active->count() - $unreported->count(),
                'unmatched_sipeka' => $this->getUnmatchedSipekaReporters($resolution['unresolved'], $fungsi),
            ],
        ];
    }

    /**
     * Terapkan filter tipe & pencarian pada koleksi personil (tidak mengubah logic matching).
     * Search: nama, NOPEK, ID Personil (mis. TAD-0001), dan jabatan — case-insensitive.
     */
    protected function applyFilters(Collection $personils, ?string $search, ?string $tipe): Collection
    {
        if ($tipe && in_array($tipe, MasterPersonil::TIPE_LIST, true)) {
            $personils = $personils->filter(fn (MasterPersonil $p) => $p->tipe_manpower === $tipe);
        }

        $needle = $search ? self::normalizeName($search) : '';
        if ($needle !== '') {
            $personils = $personils->filter(function (MasterPersonil $p) use ($needle) {
                foreach ([$p->nama, $p->nopek, $p->id_personil, $p->jabatan] as $field) {
                    if ($field !== null && str_contains(mb_strtolower((string) $field, 'UTF-8'), $needle)) {
                        return true;
                    }
                }

                return false;
            });
        }

        return $personils->values();
    }

    /**
     * Data modal: personil belum pernah melapor (paginasi) + summary + meta.
     */
    public function getUnreportedPersonils(
        string $fungsi,
        ?string $search = null,
        ?string $tipe = null,
        int $perPage = 10,
        int $page = 1
    ): array {
        $resolved = $this->resolve($fungsi);
        $filtered = $this->applyFilters($resolved['unreported'], $search, $tipe);

        $paginator = new LengthAwarePaginator(
            $filtered->forPage($page, $perPage)->values(),
            $filtered->count(),
            $perPage,
            $page
        );

        return [
            'paginator' => $paginator,
            'summary'   => $resolved['summary'],
            'meta'      => [
                'fungsi'        => $fungsi,
                'scope_label'   => 'Seluruh riwayat data SIPEKA yang tersedia',
                'sipeka_range'  => $this->getSipekaRangeLabel(),
            ],
        ];
    }

    /**
     * Seluruh record (sesuai search/filter yang sedang aktif) untuk export.
     */
    public function getUnreportedForExport(
        string $fungsi,
        ?string $search = null,
        ?string $tipe = null
    ): Collection {
        return $this->applyFilters($this->resolve($fungsi)['unreported'], $search, $tipe);
    }

    /**
     * Pelapor SIPEKA yang belum terpetakan: tidak ada mapping manual DAN tidak
     * exact-match dengan Master Personil mana pun. Tidak otomatis dimasukkan ke
     * Master Personil — ditampilkan transparan agar admin dapat melakukan mapping.
     *
     * Pada scope fungsi tertentu hanya pelapor dengan fungsi SIPEKA tsb yang tampil;
     * pada scope 'all' seluruhnya tampil.
     *
     * @param  array<string, array> $unresolved  hasil PelaporResolverService::resolve()['unresolved']
     * @return array<int, array{nama:string, fungsi:?string, jumlah:int}>
     */
    protected function getUnmatchedSipekaReporters(array $unresolved, string $fungsi): array
    {
        $out = [];
        foreach ($unresolved as $stat) {
            if ($fungsi !== self::SCOPE_ALL && $stat['fungsi'] !== $fungsi) {
                continue;
            }
            $out[] = [
                'nama'   => $stat['nama'],
                'fungsi' => $stat['fungsi'] ?? $stat['fungsi_raw'],
                'jumlah' => $stat['count'],
            ];
        }

        usort($out, fn ($a, $b) => strnatcasecmp($a['nama'], $b['nama']));

        return $out;
    }

    /**
     * Label rentang data SIPEKA yang tersedia (informasi konteks saja,
     * BUKAN batas perhitungan status pernah/belum melapor).
     */
    protected function getSipekaRangeLabel(): ?string
    {
        $range = SipekaFinding::query()
            ->selectRaw("MIN(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal'))) as mn, MAX(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal'))) as mx")
            ->first();

        if (!$range || !$range->mn || !$range->mx) {
            return null;
        }

        try {
            $fmt = fn (string $raw) => ($d = Carbon::parse($raw))->day . ' '
                . (MasterManpower::BULAN_LABELS[$d->month] ?? $d->month) . ' ' . $d->year;

            return $fmt($range->mn) . ' – ' . $fmt($range->mx);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
