<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'fungsi',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // -----------------------------------------------------------------
    // RELATIONSHIPS
    // -----------------------------------------------------------------

    /**
     * Log import yang dilakukan user ini.
     */
    public function importLogs()
    {
        return $this->hasMany(ImportLog::class);
    }

    /**
     * Riwayat perubahan finding yang dilakukan user ini.
     */
    public function findingHistories()
    {
        return $this->hasMany(FindingHistory::class, 'updated_by');
    }

    // -----------------------------------------------------------------
    // SCOPES
    // -----------------------------------------------------------------

    /**
     * Hanya user yang aktif.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Filter berdasarkan fungsi.
     */
    public function scopeByFungsi(Builder $query, string $fungsi): Builder
    {
        return $query->where('fungsi', $fungsi);
    }

    // -----------------------------------------------------------------
    // AUTHORIZATION HELPERS
    // -----------------------------------------------------------------

    /**
     * Cek apakah user memiliki role tertentu.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Cek apakah user termasuk HSSE (global access).
     * Admin HSSE dan Manager HSSE dapat melihat semua fungsi.
     */
    public function isHsseRole(): bool
    {
        return in_array($this->role, ['Admin HSSE', 'Manager HSSE']);
    }

    /**
     * Cek apakah user dapat mengedit finding berdasarkan Role + Fungsi.
     *
     * Business Rule:
     *   - Admin HSSE  → bisa edit semua finding
     *   - Admin Function → hanya bisa edit finding fungsinya sendiri
     *   - Manager HSSE / Manager Function → tidak bisa edit (read-only)
     */
    public function canEditFinding(SipekaFinding $finding): bool
    {
        if ($this->role === 'Admin HSSE') {
            return true;
        }

        if ($this->role === 'Admin Function') {
            $findingFungsi = $finding->data_sipeka['fungsi'] ?? '';
            return stripos($findingFungsi, $this->fungsi) !== false;
        }

        return false;
    }

    /**
     * Cek apakah user dapat melihat finding berdasarkan Role + Fungsi.
     *
     * Admin HSSE / Manager HSSE → semua finding
     * Admin Function / Manager Function → hanya finding fungsinya
     */
    public function canViewFinding(SipekaFinding $finding): bool
    {
        if ($this->isHsseRole()) {
            return true;
        }

        $findingFungsi = $finding->data_sipeka['fungsi'] ?? '';
        return stripos($findingFungsi, $this->fungsi) !== false;
    }
}
