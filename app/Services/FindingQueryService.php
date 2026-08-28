<?php

namespace App\Services;

use App\Models\SipekaFinding;
use App\Models\User;
use App\Models\MasterFunctionMapping;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * FindingQueryService
 *
 * Centralized query builder untuk tabel SipekaFinding.
 * Menangani Search + Filter (computed status) + Sort + Pagination secara bersamaan.
 *
 * Dipakai oleh:
 *   - SipekaFindingController (halaman /findings)
 *   - DashboardFunctionController (tabel di dashboard fungsi)
 */
class FindingQueryService
{
    /** Jumlah data per halaman default */
    private const PER_PAGE = 25;

    /**
     * Terapkan scope berdasarkan role user.
     *
     * - Admin HSSE / Manager HSSE → semua data
     * - Admin Function / Manager Function → hanya data fungsi mereka
     *
     * Filter fungsi menggunakan Master Mapping:
     *   Jika mapping tersedia → whereIn berdasarkan fungsi_sipeka yang dipetakan
     *   Jika mapping belum ada → fallback ke LIKE (behavior sebelumnya)
     */
    public function applyRoleScope(Builder $query, User $user): Builder
    {
        if (!$user->isHsseRole()) {
            $fungsi    = $user->fungsi;
            $sipValues = MasterFunctionMapping::getSipekaValues($fungsi);

            if (!empty($sipValues)) {
                // Mapping tersedia: user hanya melihat temuan yang terpetakan ke fungsinya
                $query->whereIn(
                    DB::raw("JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))"),
                    $sipValues
                );
            } else {
                // Fallback: mapping belum diinput, gunakan LIKE seperti sebelumnya
                $query->where('data_sipeka->fungsi', 'like', "%{$fungsi}%");
            }
        }

        return $query;
    }

    /**
     * Terapkan semua filter dari request (search, status, date, year, sort).
     * Dapat digabungkan dengan applyRoleScope.
     */
    public function applyFilters(Builder $query, Request $request): Builder
    {
        $this->applySearch($query, $request->get('search'));
        $this->applyStatusFilter($query, $request->get('status_filter'));
        $this->applyDateFilter($query, $request->get('date_filter'));
        $this->applyYearFilter($query, $request->get('year'));
        $this->applySorting($query, $request->get('sort_by'), $request->get('sort_dir', 'asc'));

        return $query;
    }

    /**
     * Terapkan filters dan kembalikan paginator.
     * Ini adalah method utama yang digunakan controller.
     */
    public function paginate(Builder $query, Request $request, int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        $this->applyFilters($query, $request);

        return $query->paginate($perPage)->withQueryString();
    }

    // -----------------------------------------------------------------
    // PRIVATE — Filter Methods
    // -----------------------------------------------------------------

    /**
     * Multi-kolom full-text search.
     */
    private function applySearch(Builder $query, ?string $search): void
    {
        if (empty($search)) {
            return;
        }

        $searchLower = strtolower($search);

        $query->where(function (Builder $q) use ($search, $searchLower) {
            // Kolom non-JSON (biasanya otomatis case-insensitive dari MySQL)
            $q->where('id_temuan', 'like', "%{$search}%")
              ->orWhere('no_notifikasi_sap', 'like', "%{$search}%")
              ->orWhere('keterangan_tindak_lanjut', 'like', "%{$search}%")
              // Kolom JSON (wajib di-LOWER secara eksplisit karena MySQL JSON extract menggunakan collation binary/case-sensitive)
              ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.temuan'))) LIKE ?", ["%{$searchLower}%"])
              ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi'))) LIKE ?", ["%{$searchLower}%"])
              ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor'))) LIKE ?", ["%{$searchLower}%"])
              ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.kategori'))) LIKE ?", ["%{$searchLower}%"])
              ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.unsafe_action'))) LIKE ?", ["%{$searchLower}%"])
              ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.unsafe_conditon'))) LIKE ?", ["%{$searchLower}%"])
              ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.status'))) LIKE ?", ["%{$searchLower}%"]);
        });
    }

    /**
     * Filter berdasarkan computed monitoring status (Business Rule #1).
     *
     * Computed status dihitung via SQL CASE WHEN — tidak perlu load semua record.
     *
     * @param string|null $status  'open' | 'in progress' | 'in_progress' | 'closed'
     */
    private function applyStatusFilter(Builder $query, ?string $status): void
    {
        if (empty($status)) {
            return;
        }

        $status = strtolower(trim($status));

        // Normalize "in progress" (dengan spasi)
        if ($status === 'in progress') {
            $status = 'in_progress';
        }

        match ($status) {
            'closed' => $query->whereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.status'))) = 'closed'"
            ),

            'in_progress' => $query->whereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.status'))) = 'open'"
            )->where(function (Builder $q) {
                $q->whereNotNull('no_notifikasi_sap')
                  ->where('no_notifikasi_sap', '!=', '');
            }),

            'open' => $query->whereRaw(
                "LOWER(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.status'))) = 'open'"
            )->where(function (Builder $q) {
                $q->whereNull('no_notifikasi_sap')
                  ->orWhere('no_notifikasi_sap', '');
            }),

            default => null,
        };
    }

    /**
     * Filter berdasarkan rentang tanggal relatif.
     */
    private function applyDateFilter(Builder $query, ?string $filter): void
    {
        if (empty($filter)) {
            return;
        }

        $date = match ($filter) {
            '1_day'   => now()->subDay(),
            '3_days'  => now()->subDays(3),
            '1_week'  => now()->subWeek(),
            '1_month' => now()->subMonth(),
            default   => null,
        };

        if ($date) {
            $query->where('created_at', '>=', $date);
        }
    }

    /**
     * Filter berdasarkan tahun tanggal temuan (dari JSON data_sipeka->tanggal).
     *
     * Mendukung tahun 2024, 2025, 2026, dst.
     * Menggunakan LIKE '%YYYY%' agar kompatibel dengan berbagai format tanggal di Excel.
     *
     * @param string|null $year  Tahun sebagai string, misal '2025'
     */
    private function applyYearFilter(Builder $query, ?string $year): void
    {
        if (empty($year)) {
            return;
        }

        // Validasi: pastikan hanya angka 4 digit (mencegah injection)
        if (!preg_match('/^\d{4}$/', $year)) {
            return;
        }

        $query->whereRaw(
            "JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) LIKE ?",
            ["%{$year}%"]
        );
    }

    /**
     * Sorting berdasarkan kolom yang diizinkan.
     * Default: created_at DESC (terbaru duluan).
     */
    private function applySorting(Builder $query, ?string $sortBy, string $sortDir = 'asc'): void
    {
        // Whitelist kolom yang boleh diurutkan (mencegah SQL injection)
        $allowedSortDir = in_array(strtolower($sortDir), ['asc', 'desc']) ? $sortDir : 'asc';

        match ($sortBy) {
            'status'      => $query->orderBy('data_sipeka->status', $allowedSortDir),
            'no_sap'      => $query->orderBy('no_notifikasi_sap', $allowedSortDir),
            'keterangan'  => $query->orderBy('keterangan_tindak_lanjut', $allowedSortDir),
            'tanggal'     => $query->orderByRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.tanggal')) {$allowedSortDir}"
            ),
            'fungsi'      => $query->orderBy('data_sipeka->fungsi', $allowedSortDir),
            default       => $query->latest(), // default: created_at DESC
        };
    }
}
