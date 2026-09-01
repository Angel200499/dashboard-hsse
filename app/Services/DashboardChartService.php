<?php

namespace App\Services;

use App\Models\SipekaFinding;
use App\Models\MasterFunctionMapping;
use Illuminate\Support\Facades\DB;

/**
 * DashboardChartService
 *
 * Menghasilkan data chart yang identik untuk:
 *   - Dashboard Global  → getCharts(null, $tahun)
 *   - Dashboard Function → getCharts($fungsi, $tahun)
 *
 * Semua kalkulasi dilakukan di level database (COUNT, GROUP BY, CASE WHEN).
 * Tidak ada Collection loop, tidak ada hardcoded dummy data.
 * Blade hanya menerima array PHP siap render — zero logic di Blade.
 *
 * Struktur output $charts SELALU sama:
 * [
 *   'fungsi'            => [...],   // Chart 1
 *   'reporting'         => [...],   // Chart 2
 *   'kategori'          => [...],   // Chart 3
 *   'keterlibatan'      => [...],   // Chart 4
 *   'persentase_fungsi' => [...],   // Chart 5
 *   'tindak_lanjut'     => [...],   // Chart 6
 *   'unsafe_action'     => [...],   // Chart 7
 *   'unsafe_condition'  => [...],   // Chart 8
 * ]
 */
class DashboardChartService
{
    /** Daftar fungsi yang valid */
    private const FUNGSI_LIST = ['Operation', 'Maintenance', 'HSSE', 'Business Support'];

    /**
     * Ambil semua data chart.
     *
     * @param  string|null $fungsi  Jika diisi, semua chart difilter by fungsi.
     *                              Jika null, mengambil data global.
     * @param  int|null    $tahun   Jika diisi, filter berdasarkan tahun tanggal temuan
     *                              (dari kolom JSON data_sipeka->tanggal).
     *                              Jika null, semua tahun.
     * @return array
     */
    public function getCharts(?string $fungsi = null, ?int $tahun = null): array
    {
        return [
            'fungsi'            => $this->chartJumlahPerFungsi($fungsi, $tahun),
            'fungsi_info'       => $this->chartFungsiInfo($fungsi, $tahun),
            'reporting'         => $this->chartReportingRate($fungsi, $tahun),
            'kategori'          => $this->chartKategoriPeka($fungsi, $tahun),
            'keterlibatan'      => $this->chartKeterlibatan($fungsi, $tahun),
            'persentase_fungsi' => $this->chartPersentaseFungsi($fungsi, $tahun),
            'tindak_lanjut'     => $this->chartTindakLanjut($fungsi, $tahun),
            'unsafe_action'     => $this->chartUnsafeAction($fungsi, $tahun),
            'unsafe_condition'  => $this->chartUnsafeCondition($fungsi, $tahun),
        ];
    }

    // -----------------------------------------------------------------
    // CHART METHODS
    // -----------------------------------------------------------------

    /**
     * Chart 1 — Jumlah Pelaporan per Fungsi (Pie) — data untuk chart.
     * Mengembalikan array [fungsi => count] untuk dirender Chart.js.
     */
    private function chartJumlahPerFungsi(?string $fungsi, ?int $tahun): array
    {
        $fungsiScope = $fungsi ? [$fungsi] : self::FUNGSI_LIST;
        $result = [];

        foreach ($fungsiScope as $f) {
            $count = $this->baseQuery($f, $tahun)->count();
            if ($count > 0) {
                $result[$f] = $count;
            }
        }

        return $result;
    }

    /**
     * Chart 1 — Panel Info: total, breakdown per fungsi, tertinggi, terendah.
     * Data berasal dari database (zero hardcoded).
     */
    private function chartFungsiInfo(?string $fungsi, ?int $tahun): array
    {
        $fungsiScope = $fungsi ? [$fungsi] : self::FUNGSI_LIST;
        $breakdown   = [];

        foreach ($fungsiScope as $f) {
            $count = $this->baseQuery($f, $tahun)->count();
            $breakdown[$f] = $count;
        }

        $total = array_sum($breakdown);

        $tertinggi = null;
        $terendah  = null;

        if (!empty($breakdown)) {
            $maxVal    = max($breakdown);
            $minVal    = min($breakdown);
            $tertinggi = ['fungsi' => array_search($maxVal, $breakdown), 'jumlah' => $maxVal];
            $terendah  = ['fungsi' => array_search($minVal, $breakdown), 'jumlah' => $minVal];
        }

        return [
            'total'     => $total,
            'breakdown' => $breakdown,
            'tertinggi' => $tertinggi,
            'terendah'  => $terendah,
        ];
    }

    /**
     * Chart 2 — Reporting Rate per Fungsi (Horizontal Bar).
     *
     * Formula: (temuan_fungsi_X / total_semua_temuan_scope) × 100
     * Total pembagi = total dalam scope tahun yang sama (bukan global absolut).
     */
    private function chartReportingRate(?string $fungsi, ?int $tahun): array
    {
        // Total dalam scope (dengan filter tahun jika ada)
        $totalScope = $this->baseQuery(null, $tahun)->count();

        $fungsiScope = $fungsi ? [$fungsi] : self::FUNGSI_LIST;
        $result      = [];

        if ($totalScope === 0) {
            foreach ($fungsiScope as $f) {
                $result[$f] = 0;
            }
            $result['AREA LHD'] = 0;
            return $result;
        }

        $sumScope = 0;
        foreach ($fungsiScope as $f) {
            $count = $this->baseQuery($f, $tahun)->count();
            $result[$f] = round(($count / $totalScope) * 100, 2);
            $sumScope += $count;
        }

        $result['AREA LHD'] = round(($sumScope / $totalScope) * 100, 2);

        return $result;
    }

    /**
     * Chart 3 — Kategori PEKA (Pie).
     */
    private function chartKategoriPeka(?string $fungsi, ?int $tahun): array
    {
        $kategoriList = ['Tindakan aman', 'Kondisi aman', 'Tindakan tidak aman', 'Kondisi tidak aman'];

        $rawData = $this->baseQuery($fungsi, $tahun)
            ->selectRaw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori')) as label, COUNT(*) as total")
            ->groupBy('label')
            ->orderBy('label')
            ->pluck('total', 'label')
            ->toArray();

        $result = [];
        foreach ($kategoriList as $k) {
            $result[$k] = $rawData[$k] ?? 0;
        }

        return $result;
    }

    /**
     * Chart 4 — Keterlibatan dalam Observasi (Stacked Bar %).
     *
     * % Keterlibatan = (distinct_pelapor / total_temuan_fungsi) × 100
     */
    private function chartKeterlibatan(?string $fungsi, ?int $tahun): array
    {
        $fungsiScope = $fungsi ? [$fungsi] : self::FUNGSI_LIST;
        $result      = [];

        foreach ($fungsiScope as $f) {
            $totalFungsi = $this->baseQuery($f, $tahun)->count();

            if ($totalFungsi === 0) {
                $result[$f] = 0;
                continue;
            }

            $distinctPelapor = $this->baseQuery($f, $tahun)
                ->distinct()
                ->count(DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor'))"));

            $rate       = round(($distinctPelapor / $totalFungsi) * 100);
            $result[$f] = min($rate, 100);
        }

        return $result;
    }

    /**
     * Chart 5 — Rekap Persentase Temuan per Fungsi (Stacked Bar).
     *
     * Mengembalikan ['FungsiName' => ['closed' => %, 'open' => %]]
     */
    private function chartPersentaseFungsi(?string $fungsi, ?int $tahun): array
    {
        $fungsiScope = $fungsi ? [$fungsi] : self::FUNGSI_LIST;
        $result      = [];

        foreach ($fungsiScope as $f) {
            $row = $this->baseQuery($f, $tahun)
                ->selectRaw("
                    COUNT(*) as total,
                    SUM(CASE WHEN LOWER(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.status'))) = 'closed' THEN 1 ELSE 0 END) as total_closed
                ")
                ->first();

            $total  = $row->total ?? 0;
            $closed = $row->total_closed ?? 0;

            if ($total === 0) {
                $result[$f] = ['closed' => 0, 'open' => 0];
            } else {
                $closedPct  = round(($closed / $total) * 100, 1);
                $result[$f] = [
                    'closed' => $closedPct,
                    'open'   => round(100 - $closedPct, 1),
                ];
            }
        }

        return $result;
    }

    /**
     * Chart 6 — Rekap Persentase Penindak Lanjut / By SAP (Donut).
     *
     * % distribusi finding ber-SAP per fungsi terhadap total ber-SAP dalam scope.
     */
    private function chartTindakLanjut(?string $fungsi, ?int $tahun): array
    {
        $fungsiScope = $fungsi ? [$fungsi] : self::FUNGSI_LIST;

        $totalSap = $this->baseQuery(null, $tahun)
            ->whereNotNull('no_notifikasi_sap')
            ->where('no_notifikasi_sap', '!=', '')
            ->count();

        if ($totalSap === 0) {
            $result = [];
            foreach ($fungsiScope as $f) {
                $result[$f] = 0;
            }
            return $result;
        }

        $result = [];
        foreach ($fungsiScope as $f) {
            $count = $this->baseQuery($f, $tahun)
                ->whereNotNull('no_notifikasi_sap')
                ->where('no_notifikasi_sap', '!=', '')
                ->count();
                
            $result[$f] = round(($count / $totalSap) * 100);
        }

        return $result;
    }

    /**
     * Chart 7 — Unsafe Action Category (Horizontal Bar).
     */
    private function chartUnsafeAction(?string $fungsi, ?int $tahun): array
    {
        $categories = [
            'Failure to Follow Procedure',
            'Using Improper PPE',
            'Improper Position for Task',
            'Improper Placement',
            'Operating Out of Standard',
            'Using Defective Tools/Equipments',
        ];

        return $this->buildCategoryChart('unsafe_action', $categories, $fungsi, $tahun);
    }

    /**
     * Chart 8 — Unsafe Condition Category (Horizontal Bar).
     */
    private function chartUnsafeCondition(?string $fungsi, ?int $tahun): array
    {
        $categories = [
            'Inadequate PPE',
            'Poor Housekeeping',
            'Inadequate Integrity of Equipment',
            'Restricted Space of Action',
            'Inadequate Condition of Floor/Surface',
            'Incorrect Material',
            'Inadequate Operation Mode',
            'Inadequate Guards/Barriers',
            'Improper Measurement',
            'Defective Tools/Equipments',
            'Incorrect Tools/Equipments',
            'Inadequate Warning System',
        ];

        // Catatan: kolom Excel menggunakan typo "unsafe_conditon" (tanpa 'i')
        return $this->buildCategoryChart('unsafe_conditon', $categories, $fungsi, $tahun);
    }

    /**
     * Helper — bangun data chart kategori (Chart 7 & 8).
     *
     * Menggunakan LIKE karena satu record bisa berisi beberapa sub-kategori.
     *
     * @return array  ['data' => ['Cat' => count, ...], 'total' => int]
     */
    private function buildCategoryChart(
        string $jsonKey,
        array $categories,
        ?string $fungsi,
        ?int $tahun
    ): array {
        $data  = [];
        $total = 0;

        foreach ($categories as $cat) {
            $count = $this->baseQuery($fungsi, $tahun)
                ->whereRaw(
                    "LOWER(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.{$jsonKey}'))) LIKE ?",
                    ['%' . strtolower($cat) . '%']
                )
                ->count();

            $data[$cat] = $count;
            $total     += $count;
        }

        return ['data' => $data, 'total' => $total];
    }

    // -----------------------------------------------------------------
    // REKAP PELAPOR — Business Support
    // -----------------------------------------------------------------

    /**
     * Rekap pelapor temuan Business Support.
     *
     * Menggunakan Master Mapping untuk menentukan fungsi_sipeka yang termasuk
     * Business Support — tidak ada hardcoded LIKE.
     *
     * Filter tanggal berbasis data_sipeka->tanggal (bukan created_at database),
     * agar import dari Excel dengan tanggal lama tetap difilter dengan benar.
     *
     * @param  string $periode   '' | '1_day' | '3_days' | '1_week' | '1_month' | 'per_bulan'
     * @param  int    $bulan     1-12, digunakan jika $periode === 'per_bulan'
     * @param  int    $tahun     4-digit year, digunakan jika $periode === 'per_bulan'
     * @param  string $search    Nama pelapor untuk filter (case-insensitive, partial match)
     * @return array
     */
    public function getBusinessSupportReporterRecap(
        string $periode = '',
        int $bulan = 0,
        int $tahun = 0,
        string $search = ''
    ): array {
        // 1. Scope fungsi via Master Mapping (tidak hardcode)
        $sipValues = MasterFunctionMapping::getSipekaValues('Business Support');

        $query = SipekaFinding::query();

        if (!empty($sipValues)) {
            $query->whereIn(
                DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"),
                $sipValues
            );
        } else {
            // Fallback sementara: mapping belum diinput
            $query->where('data_sipeka->fungsi', 'like', '%Business Support%');
        }

        // 2. Filter periode berdasarkan data_sipeka->tanggal
        $this->applyRekapPeriodeFilter($query, $periode, $bulan, $tahun);

        // 3. Filter nama pelapor (WHERE sebelum GROUP BY, pada kolom JSON)
        //    Menggunakan WHERE pada kolom source agar index dapat digunakan
        if (!empty(trim($search))) {
            $query->whereRaw(
                "TRIM(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor'))) LIKE ?",
                ['%' . trim($search) . '%']
            );
        }

        // 4. Query GROUP BY pelapor + COUNT DISTINCT id_temuan
        //    COALESCE agar pelapor null/kosong muncul sebagai "Tidak Diketahui"
        $rows = $query
            ->selectRaw("
                COALESCE(
                    NULLIF(TRIM(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor'))), ''),
                    'Tidak Diketahui'
                ) AS pelapor,
                COUNT(DISTINCT id_temuan) AS jumlah
            ")
            ->groupBy(DB::raw("
                COALESCE(
                    NULLIF(TRIM(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor'))), ''),
                    'Tidak Diketahui'
                )
            "))
            ->orderByDesc('jumlah')
            ->orderBy(DB::raw("
                COALESCE(
                    NULLIF(TRIM(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor'))), ''),
                    'Tidak Diketahui'
                )
            "), 'asc')
            ->get();

        $rekap          = $rows->map(fn ($r) => ['pelapor' => $r->pelapor, 'jumlah' => (int) $r->jumlah])->all();
        $totalPelaporan = array_sum(array_column($rekap, 'jumlah'));
        $totalPelapor   = count($rekap);

        $pelapor_terbanyak = !empty($rekap)
            ? ['nama' => $rekap[0]['pelapor'], 'jumlah' => $rekap[0]['jumlah']]
            : ['nama' => '-', 'jumlah' => 0];

        return [
            'rekap'             => $rekap,
            'total_pelaporan'   => $totalPelaporan,
            'total_pelapor'     => $totalPelapor,
            'pelapor_terbanyak' => $pelapor_terbanyak,
            'periode_label'     => $this->buildPeriodeLabel($periode, $bulan, $tahun),
        ];
    }

    /**
     * Terapkan filter periode ke query berdasarkan data_sipeka->tanggal.
     *
     * Format tanggal di SIPEKA bervariasi, sehingga:
     * - Filter relatif (1_day, dll.) menggunakan Carbon + STR_TO_DATE (dengan fallback LIKE)
     * - Filter per_bulan menggunakan LIKE '%YYYY-MM%' dan '%MM/YYYY%'
     */
    private function applyRekapPeriodeFilter($query, string $periode, int $bulan, int $tahun): void
    {
        if (empty($periode)) {
            return; // Semua Waktu — tanpa filter tanggal
        }

        $tanggalCol = "JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal'))";

        if ($periode === 'per_bulan' && $bulan >= 1 && $bulan <= 12 && $tahun >= 2000) {
            // Gunakan LIKE untuk mencocokkan format 'YYYY-MM' atau 'DD/MM/YYYY' dll.
            $paddedBulan = str_pad($bulan, 2, '0', STR_PAD_LEFT);
            $query->where(function ($q) use ($tanggalCol, $tahun, $paddedBulan) {
                // Format: "2026-01-..." atau "2026-01 ..."
                $q->whereRaw("{$tanggalCol} LIKE ?", ["{$tahun}-{$paddedBulan}%"])
                  ->orWhereRaw("{$tanggalCol} LIKE ?", ["%/{$tahun} {$paddedBulan}%"])
                  ->orWhereRaw("{$tanggalCol} LIKE ?", ["%{$paddedBulan}/{$tahun}%"]);
            });
            return;
        }

        // Filter relatif: gunakan Carbon untuk batas bawah tanggal
        $cutoff = match ($periode) {
            '1_day'   => now()->subDay(),
            '3_days'  => now()->subDays(3),
            '1_week'  => now()->subWeek(),
            '1_month' => now()->subMonth(),
            default   => null,
        };

        if ($cutoff) {
            // STR_TO_DATE agar bisa dibandingkan — format SIPEKA: "YYYY-MM-DD HH:MM"
            $query->whereRaw(
                "STR_TO_DATE({$tanggalCol}, '%Y-%m-%d %H:%i') >= ?",
                [$cutoff->format('Y-m-d H:i:s')]
            );
        }
    }

    /**
     * Bangun label periode untuk ditampilkan di dashboard dan PDF.
     */
    private function buildPeriodeLabel(string $periode, int $bulan, int $tahun): string
    {
        if (empty($periode)) {
            return 'Semua Waktu';
        }

        if ($periode === 'per_bulan') {
            $namaBulan = [
                1  => 'Januari', 2  => 'Februari', 3  => 'Maret',
                4  => 'April',   5  => 'Mei',       6  => 'Juni',
                7  => 'Juli',    8  => 'Agustus',   9  => 'September',
                10 => 'Oktober', 11 => 'November',  12 => 'Desember',
            ];
            $nb = $namaBulan[$bulan] ?? "Bulan {$bulan}";
            return "{$nb} {$tahun}";
        }

        return match ($periode) {
            '1_day'   => '1 Hari Terakhir',
            '3_days'  => '3 Hari Terakhir',
            '1_week'  => '1 Minggu Terakhir',
            '1_month' => '1 Bulan Terakhir',
            default   => 'Semua Waktu',
        };
    }

    // -----------------------------------------------------------------
    // BASE QUERY HELPER
    // -----------------------------------------------------------------

    /**
     * Base query dengan filter fungsi dan/atau tahun yang sudah diterapkan.
     *
     * Filter fungsi menggunakan Master Mapping:
     *   Jika mapping tersedia → WHERE fungsi_sipeka IN (...)
     *   Jika mapping belum ada → fallback ke LIKE (behavior sebelumnya)
     *
     * Fallback menjaga kompatibilitas saat mapping belum diinput Admin HSSE.
     *
     * Filter tahun menggunakan kolom JSON data_sipeka->tanggal.
     * Format tanggal di Excel SIPEKA diasumsikan mengandung tahun 4 digit (YYYY).
     *
     * @param  string|null $fungsi
     * @param  int|null    $tahun
     */
    private function baseQuery(?string $fungsi = null, ?int $tahun = null)
    {
        $query = SipekaFinding::query();

        if ($fungsi) {
            $sipValues = MasterFunctionMapping::getSipekaValues($fungsi);

            if (!empty($sipValues)) {
                // Mapping tersedia: gunakan whereIn untuk hasil yang presisi
                $query->whereIn(
                    DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"),
                    $sipValues
                );
            } else {
                // Fallback: mapping belum diinput, gunakan LIKE seperti sebelumnya
                // agar dashboard tidak rusak selama masa transisi
                $query->where('data_sipeka->fungsi', 'like', "%{$fungsi}%");
            }
        }

        if ($tahun) {
            // Filter berdasarkan tahun dari kolom JSON tanggal
            // Menggunakan LIKE '%YYYY%' karena format tanggal bisa bervariasi
            $query->whereRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE ?",
                ["%{$tahun}%"]
            );
        }

        return $query;
    }
}
