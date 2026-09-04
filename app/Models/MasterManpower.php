<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class MasterManpower extends Model
{
    use HasFactory;

    /**
     * Daftar fungsi utama yang valid.
     * Digunakan di validasi, dropdown, dan label.
     */
    public const FUNGSI_LIST = [
        'Operation',
        'Maintenance',
        'HSSE',
        'Business Support',
    ];

    /**
     * Pemetaan nomor bulan ke nama bulan (Indonesia).
     * Digunakan di view, dropdown, dan label.
     *
     * @var array<int, string>
     */
    public const BULAN_LABELS = [
        1  => 'Januari',
        2  => 'Februari',
        3  => 'Maret',
        4  => 'April',
        5  => 'Mei',
        6  => 'Juni',
        7  => 'Juli',
        8  => 'Agustus',
        9  => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'fungsi',
        'tahun',
        'bulan',
        'jumlah_manpower',
    ];

    /**
     * Cast attribute types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tahun'           => 'integer',
            'bulan'           => 'integer',
            'jumlah_manpower' => 'integer',
        ];
    }

    // -----------------------------------------------------------------
    // QUERY SCOPES
    // -----------------------------------------------------------------

    /**
     * Filter berdasarkan tahun.
     */
    public function scopeByTahun(Builder $query, int $tahun): Builder
    {
        return $query->where('tahun', $tahun);
    }

    /**
     * Filter berdasarkan bulan.
     */
    public function scopeByBulan(Builder $query, int $bulan): Builder
    {
        return $query->where('bulan', $bulan);
    }

    /**
     * Filter berdasarkan fungsi.
     */
    public function scopeByFungsi(Builder $query, string $fungsi): Builder
    {
        return $query->where('fungsi', $fungsi);
    }

    // -----------------------------------------------------------------
    // HELPERS
    // -----------------------------------------------------------------

    /**
     * Ambil jumlah manpower berdasarkan kombinasi tahun + bulan + fungsi.
     *
     * Return null jika data tidak tersedia.
     * JANGAN mengembalikan 0 sebagai default — 0 dan "tidak tersedia" memiliki arti berbeda.
     *
     * @param  int    $tahun  Tahun 4 digit (mis. 2026)
     * @param  int    $bulan  Bulan 1–12
     * @param  string $fungsi Nama fungsi (mis. 'Operation')
     * @return int|null       Jumlah manpower, atau null jika tidak tersedia
     */
    public static function getManpower(int $tahun, int $bulan, string $fungsi): ?int
    {
        $record = static::where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->where('fungsi', $fungsi)
            ->first();

        return $record?->jumlah_manpower;
    }

    /**
     * Kembalikan daftar tahun yang sudah memiliki data manpower.
     * Digunakan untuk filter dropdown pada halaman Master Manpower.
     *
     * @return \Illuminate\Support\Collection<int, int>
     */
    public static function availableYears(): \Illuminate\Support\Collection
    {
        return static::distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun');
    }

    /**
     * Kembalikan daftar bulan yang sudah memiliki data manpower untuk tahun tertentu.
     * Bulan null (data lama) dikecualikan.
     *
     * @param  int $tahun
     * @return \Illuminate\Support\Collection<int, int>
     */
    public static function availableMonths(int $tahun): \Illuminate\Support\Collection
    {
        return static::where('tahun', $tahun)
            ->whereNotNull('bulan')
            ->distinct()
            ->orderBy('bulan')
            ->pluck('bulan');
    }

    /**
     * Kembalikan nama bulan dari nomor bulan.
     *
     * @param  int|null $bulan
     * @return string
     */
    public static function namaBulan(?int $bulan): string
    {
        if ($bulan === null) {
            return '—';
        }
        return self::BULAN_LABELS[$bulan] ?? "Bulan {$bulan}";
    }
}
