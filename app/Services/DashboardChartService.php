<?php

namespace App\Services;

use App\Models\SipekaFinding;
use App\Models\MasterFunctionMapping;
use App\Models\MasterManpower;
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
 *   'fungsi_info'       => [...],   // Panel info Chart 1
 *   'reporting_lhd'     => [...],   // Chart 2a — Reporting Rate Area LHD (NEW)
 *   'reporting'         => [...],   // Chart 2b — Reporting Rate per Fungsi
 *   'trending'          => [...],   // Chart Trending Temuan (NEW)
 *   'kategori'          => [...],   // Chart 3
 *   'keterlibatan'      => [...],   // Chart 4
 *   'persentase_fungsi' => [...],   // Chart 5
 *   'tindak_lanjut'     => [...],   // Chart 6
 *   'unsafe_action'     => [...],   // Chart 7
 *   'unsafe_condition'  => [...],   // Chart 8
 * ]
 *
 * PENTING — ISOLASI QUERY:
 * baseQuery() digunakan oleh Chart 1,3,4,5,6,7,8 dan KPI.
 * chartReportingRate() dan chartReportingRateLhd() menggunakan
 * ytdQuery() yang terpisah — jangan mengubah baseQuery() untuk
 * keperluan Reporting Rate.
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
     * @param  int|null    $tahun   Jika diisi, filter berdasarkan tahun tanggal temuan.
     *                              Jika null, semua tahun.
     * @param  int|null    $bulan   Jika diisi, Reporting Rate menggunakan manpower bulan tersebut
     *                              dan temuan dihitung YTD (Januari–bulan).
     *                              Chart lain (Kategori PEKA, Unsafe, dll.) TIDAK terpengaruh.
     *                              Jika null, Reporting Rate menampilkan mode distribusi.
     * @return array
     */
    public function getCharts(?string $fungsi = null, ?int $tahun = null, ?int $bulan = null): array
    {
        return [
            'fungsi'            => $this->chartJumlahPerFungsi($fungsi, $tahun),
            'fungsi_info'       => $this->chartFungsiInfo($fungsi, $tahun),
            'reporting_lhd'     => $this->chartReportingRateLhd($tahun, $bulan),
            'reporting'         => $this->chartReportingRate($fungsi, $tahun, $bulan),
            'trending'          => $this->chartTrendingTemuan($fungsi, $tahun),
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
     * Chart 2a — Reporting Rate Area LHD (NEW).
     *
     * Menghitung Reporting Rate untuk seluruh area LHD (semua fungsi gabungan).
     *
     * Formula:
     *   Rate = (total_temuan_Jan_sd_bulan / (total_manpower_semua_fungsi × bulan)) × 100
     *
     * total_manpower = jumlah manpower seluruh fungsi pada bulan yang dipilih.
     * BUKAN jumlah kumulatif manpower per bulan.
     *
     * @param  int|null $tahun
     * @param  int|null $bulan  1–12; null → mode tidak aktif
     * @return array
     */
    private function chartReportingRateLhd(?int $tahun, ?int $bulan): array
    {
        // Jika tidak ada tahun atau bulan yang dipilih, kembalikan mode tidak aktif
        if (!$tahun || !$bulan) {
            return [
                'aktif'          => false,
                'rate'           => null,
                'total_temuan'   => null,
                'total_manpower' => null,
                'jumlah_bulan'   => null,
                'tersedia'       => false,
                'periode_label'  => null,
            ];
        }

        // 1. Hitung total temuan YTD (Januari s/d bulan terpilih, seluruh fungsi)
        $totalTemuan = $this->ytdQuery(null, $tahun, $bulan)->count();

        // 2. Hitung total manpower seluruh fungsi pada bulan terpilih
        $totalManpower = 0;
        $manpowerTersedia = true;

        foreach (self::FUNGSI_LIST as $f) {
            $mp = MasterManpower::getManpower($tahun, $bulan, $f);
            if ($mp === null) {
                $manpowerTersedia = false;
                break;
            }
            $totalManpower += $mp;
        }

        // 3. Hitung rate
        $rate = null;
        if ($manpowerTersedia && $totalManpower > 0) {
            $denominator = $totalManpower * $bulan;
            $rate = round(($totalTemuan / $denominator) * 100, 2);
        }

        // 4. Label periode
        $namaBulan = MasterManpower::BULAN_LABELS[$bulan] ?? "Bulan {$bulan}";
        $periodeLabel = "Januari–{$namaBulan} {$tahun}";

        return [
            'aktif'          => true,
            'rate'           => $rate,
            'total_temuan'   => $totalTemuan,
            'total_manpower' => $manpowerTersedia ? $totalManpower : null,
            'jumlah_bulan'   => $bulan,
            'tersedia'       => $manpowerTersedia,
            'periode_label'  => $periodeLabel,
        ];
    }

    /**
     * Chart 2b — Reporting Rate per Fungsi (Horizontal Bar).
     *
     * Formula (saat bulan dipilih):
     *   Rate_fungsi = (total_temuan_YTD_fungsi / (manpower_fungsi_bulan × bulan)) × 100
     *
     * Formula (saat bulan tidak dipilih / mode distribusi):
     *   Menampilkan distribusi jumlah pelaporan per fungsi (backward compatible).
     *
     * PENTING:
     *   - ytdQuery() digunakan untuk menghitung temuan YTD — terpisah dari baseQuery()
     *   - Manpower yang digunakan adalah manpower BULAN yang dipilih (bukan kumulatif)
     *   - Jika manpower tidak tersedia, rate = null (bukan 0)
     */
    private function chartReportingRate(?string $fungsi, ?int $tahun, ?int $bulan = null): array
    {
        $fungsiList = $fungsi ? [$fungsi] : self::FUNGSI_LIST;

        // Mode: tidak ada tahun atau bulan → tampilkan distribusi lama
        if (!$tahun || !$bulan) {
            $data = [];
            $sumScopeTemuan   = 0;
            $sumScopeManpower = 0;

            foreach ($fungsiList as $f) {
                $jumlahPelaporan  = $this->baseQuery($f, $tahun)->count();
                $sumScopeTemuan  += $jumlahPelaporan;

                $mpQuery = MasterManpower::where('fungsi', $f);
                if ($tahun) {
                    $mpQuery->where('tahun', $tahun);
                }
                $manpower = (int) $mpQuery->sum('jumlah_manpower');
                $sumScopeManpower += $manpower;

                if ($manpower > 0) {
                    $data[$f] = round($jumlahPelaporan / $manpower, 2);
                } else {
                    $data[$f] = 0;
                }
            }

            // AREA LHD — hanya untuk global (fungsi = null)
            if (!$fungsi) {
                if ($sumScopeManpower > 0) {
                    $data['AREA LHD'] = round($sumScopeTemuan / $sumScopeManpower, 2);
                } else {
                    $data['AREA LHD'] = 0;
                }

                // Urutkan: AREA LHD di atas
                $ordered = ['AREA LHD' => $data['AREA LHD']];
                foreach (self::FUNGSI_LIST as $f) {
                    $ordered[$f] = $data[$f] ?? 0;
                }
                $data = $ordered;
            }

            return [
                'mode'          => 'distribusi',
                'data'          => $data,
                'periode_label' => $tahun ? "Tahun {$tahun}" : 'Semua Waktu',
            ];
        }

        // Mode: tahun + bulan dipilih → gunakan formula YTD dengan manpower bulanan
        $data           = [];
        $namaBulan      = MasterManpower::BULAN_LABELS[$bulan] ?? "Bulan {$bulan}";
        $periodeLabel   = "Januari–{$namaBulan} {$tahun}";

        foreach ($fungsiList as $f) {
            // Temuan YTD: Januari s/d bulan terpilih
            $totalTemuan = $this->ytdQuery($f, $tahun, $bulan)->count();

            // Manpower: hanya bulan yang dipilih (bukan kumulatif)
            $manpower = MasterManpower::getManpower($tahun, $bulan, $f);

            if ($manpower === null) {
                // Manpower tidak tersedia — jangan menghitung
                $data[$f] = null;
            } elseif ($manpower === 0) {
                // Manpower nol — hindari division by zero
                $data[$f] = null;
            } else {
                $denominator = $manpower * $bulan;
                $data[$f]    = round(($totalTemuan / $denominator) * 100, 2);
            }
        }

        // Untuk global dashboard, tambahkan AREA LHD dari chartReportingRateLhd
        // (tidak perlu dihitung ulang di sini, sudah ada di key 'reporting_lhd')

        return [
            'mode'          => 'manpower_rasio',
            'data'          => $data,
            'periode_label' => $periodeLabel,
        ];
    }

    /**
     * Chart Trending Temuan — Line Chart 12 bulan (NEW).
     *
     * Menampilkan jumlah temuan per bulan sepanjang tahun yang dipilih.
     * Selalu mengembalikan 12 elemen (Jan–Des), bulan tanpa data = 0.
     *
     * PENTING: Chart ini hanya mengikuti filter TAHUN, bukan filter bulan.
     * Dropdown bulan untuk Reporting Rate tidak memengaruhi chart ini.
     *
     * @param  string|null $fungsi
     * @param  int|null    $tahun   Jika null, kembalikan semua 0
     * @return array  [1=>int, 2=>int, ..., 12=>int]
     */
    private function chartTrendingTemuan(?string $fungsi, ?int $tahun): array
    {
        // Inisialisasi 12 bulan dengan 0
        $result = array_fill(1, 12, 0);

        if (!$tahun) {
            return $result;
        }

        // Query: GROUP BY bulan dari tanggal temuan
        // Filter hanya berdasarkan tahun (bukan bulan spesifik)
        $query = SipekaFinding::query();

        // Filter fungsi jika ada
        if ($fungsi) {
            $sipValues = MasterFunctionMapping::getSipekaValues($fungsi);
            if (!empty($sipValues)) {
                $query->whereIn(
                    DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"),
                    $sipValues
                );
            } else {
                $query->where('data_sipeka->fungsi', 'like', "%{$fungsi}%");
            }
        }

        // Filter tahun via LIKE pada kolom tanggal
        $tanggalCol = "JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal'))";
        $query->whereRaw("{$tanggalCol} LIKE ?", ["%{$tahun}%"]);

        // GROUP BY bulan (MONTH dari tanggal format YYYY-MM-DD HH:MM)
        $rows = $query
            ->selectRaw(
                "MONTH(STR_TO_DATE({$tanggalCol}, '%Y-%m-%d %H:%i')) as bulan_ke,
                 COUNT(*) as total"
            )
            ->groupBy('bulan_ke')
            ->orderBy('bulan_ke')
            ->get();

        foreach ($rows as $row) {
            $bln = (int) $row->bulan_ke;
            if ($bln >= 1 && $bln <= 12) {
                $result[$bln] = (int) $row->total;
            }
        }

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
     * Base query dengan filter fungsi dan tahun yang sudah diterapkan.
     *
     * PENTING: baseQuery() digunakan oleh Chart 1, 3, 4, 5, 6, 7, 8 dan KPI.
     * Jangan mengubah perilaku baseQuery() untuk keperluan Reporting Rate.
     * Gunakan ytdQuery() khusus untuk Reporting Rate.
     *
     * Filter fungsi menggunakan Master Mapping:
     *   Jika mapping tersedia → WHERE fungsi_sipeka IN (...)
     *   Jika mapping belum ada → fallback ke LIKE (behavior sebelumnya)
     *
     * Filter tahun menggunakan kolom JSON data_sipeka->tanggal.
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

        $tanggalCol = "JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal'))";

        if ($tahun) {
            // Filter berdasarkan tahun saja (tidak per bulan)
            $query->whereRaw(
                "{$tanggalCol} LIKE ?",
                ["%{$tahun}%"]
            );
        }

        return $query;
    }

    /**
     * YTD Query — khusus untuk Reporting Rate.
     *
     * Berbeda dari baseQuery(): query ini memfilter temuan dari Januari
     * sampai bulan yang dipilih (year-to-date / kumulatif).
     *
     * Digunakan oleh chartReportingRate() dan chartReportingRateLhd().
     * JANGAN gunakan untuk chart lain agar tidak mengubah perilaku existing.
     *
     * @param  string|null $fungsi  null = semua fungsi (Area LHD)
     * @param  int         $tahun
     * @param  int         $bulan   1–12
     */
    private function ytdQuery(?string $fungsi, int $tahun, int $bulan)
    {
        $query = SipekaFinding::query();

        // Filter fungsi
        if ($fungsi) {
            $sipValues = MasterFunctionMapping::getSipekaValues($fungsi);
            if (!empty($sipValues)) {
                $query->whereIn(
                    DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"),
                    $sipValues
                );
            } else {
                $query->where('data_sipeka->fungsi', 'like', "%{$fungsi}%");
            }
        }

        $tanggalCol = "JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal'))";

        // Filter YTD: Januari (01) s/d bulan terpilih
        // Format tanggal: 'YYYY-MM-DD HH:MM' → gunakan STR_TO_DATE untuk range
        $startDate = "{$tahun}-01-01 00:00";
        $endPadded = str_pad($bulan, 2, '0', STR_PAD_LEFT);
        // Hitung hari terakhir bulan terpilih
        $lastDay   = date('t', mktime(0, 0, 0, $bulan, 1, $tahun));
        $endDate   = "{$tahun}-{$endPadded}-{$lastDay} 23:59";

        $query->whereRaw(
            "STR_TO_DATE({$tanggalCol}, '%Y-%m-%d %H:%i') BETWEEN ? AND ?",
            [$startDate, $endDate]
        );

        return $query;
    }
}
