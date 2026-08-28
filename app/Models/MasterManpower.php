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
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'fungsi',
        'tahun',
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
}
