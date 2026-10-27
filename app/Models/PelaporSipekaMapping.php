<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Mapping MANUAL nama pelapor SIPEKA → Master Personil.
 *
 * Hanya layer relasi. Tidak pernah mengubah data_sipeka.pelapor.
 */
class PelaporSipekaMapping extends Model
{
    protected $table = 'pelapor_sipeka_mappings';

    protected $fillable = [
        'nama_sipeka',
        'nama_sipeka_normalized',
        'master_personil_id',
        'created_by',
        'updated_by',
    ];

    public function personil(): BelongsTo
    {
        return $this->belongsTo(MasterPersonil::class, 'master_personil_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Normalisasi aman: trim + rapikan whitespace berulang + lowercase.
     * Tanpa fuzzy matching.
     */
    public static function normalize(?string $name): string
    {
        $name = preg_replace('/\s+/u', ' ', (string) $name) ?? '';

        return mb_strtolower(trim($name), 'UTF-8');
    }
}
