<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FindingHistory extends Model
{
    protected $fillable = [
        'finding_id',
        'updated_by',
        'old_no_notifikasi_sap',
        'new_no_notifikasi_sap',
        'old_keterangan_tindak_lanjut',
        'new_keterangan_tindak_lanjut',
    ];

    /**
     * Finding yang diubah.
     */
    public function finding()
    {
        return $this->belongsTo(SipekaFinding::class, 'finding_id');
    }

    /**
     * User yang melakukan perubahan.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
