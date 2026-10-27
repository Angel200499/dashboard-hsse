<?php

namespace App\Services;

use App\Models\MasterFunctionMapping;
use App\Models\MasterPersonil;
use App\Models\PelaporSipekaMapping;
use App\Models\SipekaFinding;

/**
 * Resolusi nama pelapor SIPEKA → Master Personil.
 *
 * STANDALONE: hanya melayani "Daftar Belum Melapor" & halaman Mapping Pelapor.
 * TIDAK dipakai oleh DashboardChartService / grafik Keterlibatan. Hanya MEMBACA
 * sipeka_findings — tidak pernah mengubah data_sipeka.pelapor.
 *
 * Urutan resolusi per nama pelapor SIPEKA:
 *   1. Mapping manual   (nama_sipeka_normalized → master_personil_id)
 *   2. Exact match nama ternormalisasi dengan Master Personil
 *   3. Tidak ditemukan  → "belum terpetakan"
 * Tidak ada fuzzy matching.
 */
class PelaporResolverService
{
    /** @var array<string, array>|null */
    protected ?array $statsCache = null;

    public static function normalize(?string $name): string
    {
        return PelaporSipekaMapping::normalize($name);
    }

    /**
     * Statistik seluruh nama pelapor SIPEKA (SATU query agregat).
     *
     * @return array<string, array{key:string, nama:string, count:int, fungsi:?string, fungsi_raw:?string}>
     *         key = nama ternormalisasi
     */
    public function reporterStats(): array
    {
        if ($this->statsCache !== null) {
            return $this->statsCache;
        }

        $rows = SipekaFinding::query()
            ->selectRaw("
                NULLIF(TRIM(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor'))), '') as pelapor,
                NULLIF(TRIM(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))), '') as fungsi,
                COUNT(*) as total
            ")
            ->groupBy('pelapor', 'fungsi')
            ->get();

        // fungsi_sipeka (lowercase) => fungsi dashboard (4 fungsi utama). GM/tak terpetakan → tidak masuk.
        $fungsiMap = MasterFunctionMapping::query()
            ->whereNotNull('fungsi_dashboard')
            ->get(['fungsi_sipeka', 'fungsi_dashboard'])
            ->mapWithKeys(fn ($m) => [mb_strtolower(trim($m->fungsi_sipeka), 'UTF-8') => $m->fungsi_dashboard])
            ->all();

        $acc = [];
        foreach ($rows as $row) {
            $key = self::normalize($row->pelapor);
            if ($key === '' || $key === 'null') {
                continue;
            }

            $display   = preg_replace('/\s+/u', ' ', trim((string) $row->pelapor));
            $rawFungsi = $row->fungsi ? trim((string) $row->fungsi) : null;
            if ($rawFungsi !== null && mb_strtolower($rawFungsi, 'UTF-8') === 'null') {
                $rawFungsi = null; // JSON null ter-unquote menjadi string 'null'
            }
            $dash      = $rawFungsi ? ($fungsiMap[mb_strtolower($rawFungsi, 'UTF-8')] ?? null) : null;
            $total     = (int) $row->total;

            $acc[$key]['nama']  ??= $display;
            $acc[$key]['count']  = ($acc[$key]['count'] ?? 0) + $total;
            if ($dash) {
                $acc[$key]['dash'][$dash] = ($acc[$key]['dash'][$dash] ?? 0) + $total;
            }
            if ($rawFungsi) {
                $acc[$key]['raw'][$rawFungsi] = ($acc[$key]['raw'][$rawFungsi] ?? 0) + $total;
            }
        }

        $stats = [];
        foreach ($acc as $key => $a) {
            $dash = null;
            if (!empty($a['dash'])) {
                arsort($a['dash']);
                $dash = array_key_first($a['dash']);
            }
            $raw = null;
            if (!empty($a['raw'])) {
                arsort($a['raw']);
                $raw = array_key_first($a['raw']);
            }

            $stats[$key] = [
                'key'        => $key,
                'nama'       => $a['nama'],
                'count'      => $a['count'],
                'fungsi'     => $dash,   // salah satu dari 4 fungsi utama (atau null)
                'fungsi_raw' => $raw,    // nilai asli SIPEKA, fallback tampilan
            ];
        }

        return $this->statsCache = $stats;
    }

    /**
     * Mapping manual: [nama_sipeka_normalized => master_personil_id].
     *
     * @return array<string, int>
     */
    public function manualMappings(): array
    {
        return PelaporSipekaMapping::query()
            ->pluck('master_personil_id', 'nama_sipeka_normalized')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /**
     * Resolusi seluruh pelapor SIPEKA.
     *
     * @return array{
     *   reported_ids: array<int,bool>,         master_personil_id yang punya riwayat laporan
     *   unresolved: array<string,array>,       pelapor SIPEKA belum terpetakan (key => stat)
     *   manual_count: int,
     *   exact_count: int
     * }
     */
    public function resolve(): array
    {
        $stats    = $this->reporterStats();
        $mappings = $this->manualMappings();

        $byName = [];
        foreach (MasterPersonil::query()->get(['id', 'nama']) as $p) {
            $byName[self::normalize($p->nama)][] = (int) $p->id;
        }

        $reported   = [];
        $unresolved = [];
        $manual     = 0;
        $exact      = 0;

        foreach ($stats as $key => $stat) {
            if (isset($mappings[$key])) {                 // Prioritas 1 — manual
                $reported[$mappings[$key]] = true;
                $manual++;
            } elseif (isset($byName[$key])) {             // Prioritas 2 — exact normalized
                foreach ($byName[$key] as $id) {
                    $reported[$id] = true;
                }
                $exact++;
            } else {                                      // Prioritas 3 — belum terpetakan
                $unresolved[$key] = $stat;
            }
        }

        return [
            'reported_ids' => $reported,
            'unresolved'   => $unresolved,
            'manual_count' => $manual,
            'exact_count'  => $exact,
        ];
    }
}
