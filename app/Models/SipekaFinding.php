<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SipekaFinding extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_temuan',
        'no_notifikasi_sap',
        'keterangan_tindak_lanjut',
        'data_sipeka'
    ];

    protected $casts = [
        'data_sipeka' => 'array',
    ];
}
