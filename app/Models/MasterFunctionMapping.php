<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class MasterFunctionMapping extends Model
{
    use HasFactory;

    /**
     * 4 fungsi utama Dashboard HSSE.
     * Digunakan di validasi, dropdown, dan label.
     */
    public const DASHBOARD_FUNGSI_LIST = [
        'Operation',
        'Maintenance',
        'HSSE',
        'Business Support',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'fungsi_sipeka',
        'fungsi_dashboard',
    ];

    // -----------------------------------------------------------------
    // QUERY SCOPES
    // -----------------------------------------------------------------

    /**
     * Filter berdasarkan fungsi dashboard tujuan.
     */
    public function scopeByDashboard(Builder $query, string $fungsiDashboard): Builder
    {
        return $query->where('fungsi_dashboard', $fungsiDashboard);
    }

    // -----------------------------------------------------------------
    // STATIC HELPERS — Digunakan oleh Dashboard Services
    // -----------------------------------------------------------------

    /**
     * Kembalikan seluruh nilai fungsi_sipeka yang dipetakan ke $fungsiDashboard.
     *
     * Digunakan oleh DashboardChartService, DashboardFunctionController,
     * dan FindingQueryService untuk filter query berbasis mapping.
     *
     * Contoh:
     *   getSipekaValues('Operation')
     *   → ['Operation LHD', 'OPERATION', 'Operator']
     *
     * @param  string $fungsiDashboard  Salah satu dari DASHBOARD_FUNGSI_LIST
     * @return array<int, string>       Array nilai fungsi_sipeka; kosong jika belum ada mapping
     */
    public static function getSipekaValues(string $fungsiDashboard): array
    {
        return static::where('fungsi_dashboard', $fungsiDashboard)
            ->pluck('fungsi_sipeka')
            ->toArray();
    }

    /**
     * Kembalikan daftar nilai FUNGSI dari sipeka_findings yang belum memiliki mapping.
     *
     * Query ini HANYA membaca data — tidak mengubah sipeka_findings sama sekali.
     *
     * Perbandingan dilakukan secara CASE-INSENSITIVE agar sinkron dengan
     * perilaku unique constraint MySQL (case-insensitive collation).
     * Contoh: jika "OPERATION" sudah dipetakan, "Operation" tidak akan
     * muncul sebagai unmapped meskipun string-nya berbeda secara huruf.
     *
     * Langkah:
     *  1. Ambil distinct nilai fungsi dari JSON column data_sipeka->fungsi
     *  2. Hapus NULL dan string kosong
     *  3. Bandingkan (case-insensitive) dengan fungsi_sipeka yang sudah ada
     *  4. Kembalikan yang belum dipetakan
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    public static function unmappedFungsi(): \Illuminate\Support\Collection
    {
        // Semua nilai fungsi unik dari sipeka_findings (hanya baca)
        $allSipekaFungsi = DB::table('sipeka_findings')
            ->selectRaw("DISTINCT JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.fungsi')) as fungsi")
            ->pluck('fungsi')
            ->filter(fn ($v) => !is_null($v) && trim($v) !== '') // hapus null & kosong
            ->values();

        // Semua fungsi_sipeka yang sudah dipetakan — lowercase untuk perbandingan
        // Case-insensitive: agar "OPERATION" dan "Operation" dianggap sama
        $mappedLower = static::pluck('fungsi_sipeka')
            ->map(fn ($v) => strtolower(trim($v)))
            ->toArray();

        // Kembalikan yang belum dipetakan (case-insensitive comparison)
        return $allSipekaFungsi
            ->filter(fn ($v) => !in_array(strtolower(trim($v)), $mappedLower))
            ->sort()
            ->values();
    }
}
