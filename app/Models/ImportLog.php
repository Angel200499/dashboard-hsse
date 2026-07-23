<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    protected $fillable = [
        'user_id',
        'filename',
        'jumlah_insert',
        'jumlah_update',
        'jumlah_skip',
        'jumlah_error',
        'durasi_detik',
        'status',
        'catatan',
    ];

    protected $casts = [
        'jumlah_insert' => 'integer',
        'jumlah_update' => 'integer',
        'jumlah_skip'   => 'integer',
        'jumlah_error'  => 'integer',
        'durasi_detik'  => 'decimal:3',
    ];

    /**
     * Pengguna yang melakukan import.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
