<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class MasterPersonil extends Model
{
    use HasFactory;

    /**
     * Daftar 4 fungsi utama dashboard HSSE.
     */
    public const FUNGSI_LIST = [
        'Operation',
        'Maintenance',
        'HSSE',
        'Business Support',
    ];

    /**
     * Daftar tipe manpower yang valid.
     */
    public const TIPE_LIST = [
        'Pekerja',
        'TAD',
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'master_personils';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'nopek',
        'fungsi',
        'jabatan',
        'tipe_manpower',
        'status_aktif',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status_aktif' => 'boolean',
        ];
    }

    /**
     * ID Personil internal (bukan NOPEK resmi), diturunkan dari master_personils.id.
     * Contoh: TAD-0001 / PKJ-0001. Tidak pernah dipakai untuk matching dengan SIPEKA.
     */
    public function getIdPersonilAttribute(): string
    {
        return ($this->tipe_manpower === 'TAD' ? 'TAD' : 'PKJ') . '-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }


    // -----------------------------------------------------------------
    // QUERY SCOPES
    // -----------------------------------------------------------------

    /**
     * Hanya personil dengan status aktif.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status_aktif', true);
    }

    /**
     * Filter berdasarkan fungsi.
     */
    public function scopeByFungsi(Builder $query, string $fungsi): Builder
    {
        return $query->where('fungsi', $fungsi);
    }

    /**
     * Filter berdasarkan tipe manpower (Pekerja / TAD).
     */
    public function scopeByTipe(Builder $query, string $tipe): Builder
    {
        return $query->where('tipe_manpower', $tipe);
    }

    /**
     * Pencarian multi-kolom (nama, nopek, jabatan).
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        $search = trim($search);
        return $query->where(function (Builder $q) use ($search) {
            $q->where('nama', 'like', "%{$search}%")
              ->orWhere('nopek', 'like', "%{$search}%")
              ->orWhere('jabatan', 'like', "%{$search}%");
        });
    }

    // -----------------------------------------------------------------
    // BUSINESS LOGIC & HELPERS
    // -----------------------------------------------------------------

    /**
     * Cek apakah personil memiliki riwayat laporan di SIPEKA
     * berdasarkan pencocokan nama (case-insensitive).
     */
    public function hasReportHistory(): bool
    {
        $namaClean = strtolower(trim((string) $this->nama));
        if ($namaClean === '') {
            return false;
        }

        return SipekaFinding::whereRaw(
            "LOWER(TRIM(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor')))) = ?",
            [$namaClean]
        )->exists();
    }

    /**
     * Hitung jumlah temuan laporan di SIPEKA yang dibuat oleh personil ini.
     */
    public function reportsCount(): int
    {
        $namaClean = strtolower(trim((string) $this->nama));
        if ($namaClean === '') {
            return 0;
        }

        return SipekaFinding::whereRaw(
            "LOWER(TRIM(JSON_UNQUOTE(JSON_EXTRACT(data_sipeka, '$.pelapor')))) = ?",
            [$namaClean]
        )->count();
    }
}
