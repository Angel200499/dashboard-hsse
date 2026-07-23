<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class SipekaFinding extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_temuan',
        'no_notifikasi_sap',
        'keterangan_tindak_lanjut',
        'data_sipeka',
    ];

    protected $casts = [
        'data_sipeka' => 'array',
    ];

    // -----------------------------------------------------------------
    // RELATIONSHIPS
    // -----------------------------------------------------------------

    /**
     * Riwayat perubahan No. SAP & Keterangan Tindak Lanjut.
     */
    public function histories()
    {
        return $this->hasMany(FindingHistory::class, 'finding_id');
    }

    // -----------------------------------------------------------------
    // COMPUTED ATTRIBUTE — Monitoring Status (Business Rule #1)
    //
    // Closed      → status SIPEKA = 'closed'
    // In Progress → status SIPEKA = 'open' AND no_notifikasi_sap terisi
    // Open        → status SIPEKA = 'open' AND no_notifikasi_sap kosong
    // -----------------------------------------------------------------

    /**
     * Kembalikan monitoring status berdasarkan business rule HSSE.
     * Digunakan di Blade dan Export.
     */
    public function getMonitoringStatusAttribute(): string
    {
        $sipeka = strtolower(trim($this->data_sipeka['status'] ?? ''));

        if ($sipeka === 'closed') {
            return 'Closed';
        }

        if (!empty(trim($this->no_notifikasi_sap ?? ''))) {
            return 'In Progress';
        }

        return 'Open';
    }

    // -----------------------------------------------------------------
    // QUERY SCOPES
    // -----------------------------------------------------------------

    /**
     * Filter berdasarkan fungsi (dari JSON column data_sipeka->fungsi).
     */
    public function scopeByFungsi(Builder $query, string $fungsi): Builder
    {
        return $query->where('data_sipeka->fungsi', $fungsi);
    }

    /**
     * Filter berdasarkan computed monitoring status (business rule HSSE).
     *
     * Status diperhitungkan via SQL CASE WHEN agar kompatibel dengan pagination
     * dan tidak perlu load semua record ke PHP.
     *
     * @param string $status  'open' | 'in_progress' | 'in progress' | 'closed'
     */
    public function scopeMonitoringStatus(Builder $query, string $status): Builder
    {
        $status = strtolower(trim($status));

        // Normalize "in progress" (dengan spasi) ke "in_progress"
        if ($status === 'in progress') {
            $status = 'in_progress';
        }

        return match ($status) {
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

            default => $query,
        };
    }

    /**
     * Multi-kolom search pada kolom JSON dan kolom native.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function (Builder $q) use ($search) {
            $q->where('id_temuan', 'like', "%{$search}%")
              ->orWhere('no_notifikasi_sap', 'like', "%{$search}%")
              ->orWhere('keterangan_tindak_lanjut', 'like', "%{$search}%")
              ->orWhere('data_sipeka->temuan', 'like', "%{$search}%")
              ->orWhere('data_sipeka->fungsi', 'like', "%{$search}%")
              ->orWhere('data_sipeka->pelapor', 'like', "%{$search}%")
              ->orWhere('data_sipeka->kategori', 'like', "%{$search}%")
              ->orWhere('data_sipeka->unsafe_action', 'like', "%{$search}%")
              ->orWhere('data_sipeka->unsafe_conditon', 'like', "%{$search}%")
              ->orWhere('data_sipeka->status', 'like', "%{$search}%");
        });
    }

    /**
     * Filter berdasarkan rentang tanggal relatif.
     *
     * @param string $filter  '1_day' | '3_days' | '1_week' | '1_month'
     */
    public function scopeDateFilter(Builder $query, string $filter): Builder
    {
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

        return $query;
    }
}
