<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ReportedPersonilModalTest extends TestCase
{
    protected function getOrCreateUser(string $username, string $role, ?string $fungsi = null): User
    {
        return User::firstOrCreate(
            ['username' => $username],
            [
                'name'     => ucfirst(str_replace('_', ' ', $username)),
                'password' => bcrypt('password'),
                'role'     => $role,
                'fungsi'   => $fungsi,
            ]
        );
    }

    public function test_reported_modal_pagination_and_data_scope(): void
    {
        $adminOp = $this->getOrCreateUser('test_admin_op', 'Admin Function', 'Operation');
        $adminHsse = $this->getOrCreateUser('test_admin_hsse', 'Admin HSSE', 'HSSE');

        // 1. Admin Operation can access own function
        $resOpSelf = $this->actingAs($adminOp)->get(route('reported-personil.data', [
            'fungsi'   => 'Operation',
            'page'     => 1,
            'per_page' => 10,
        ]));
        $resOpSelf->assertStatus(200);
        $resOpSelf->assertJsonStructure([
            'success',
            'meta',
            'summary' => [
                'total_active',
                'total_unreported',
                'total_reported',
            ],
            'data',
            'pagination' => [
                'current_page',
                'last_page',
                'per_page',
                'total',
            ],
        ]);

        // 2. Admin Operation CANNOT access Maintenance (403)
        $resOpOther = $this->actingAs($adminOp)->get(route('reported-personil.data', [
            'fungsi'   => 'Maintenance',
            'page'     => 1,
            'per_page' => 10,
        ]));
        $resOpOther->assertStatus(403);

        // 3. Admin HSSE can access any function and 'all'
        $resHsse = $this->actingAs($adminHsse)->get(route('reported-personil.data', [
            'fungsi'   => 'all',
            'page'     => 1,
            'per_page' => 10,
        ]));
        $resHsse->assertStatus(200);

        // 4. Export CSV works
        $resExport = $this->actingAs($adminOp)->get(route('reported-personil.export', [
            'fungsi' => 'Operation',
        ]));
        $resExport->assertStatus(200);
        $this->assertTrue(str_contains($resExport->headers->get('content-type') ?? '', 'text/csv'));

        // 5. Konsistensi klasifikasi: total_reported + total_unreported == total_active
        $summary = $resOpSelf->json('summary');
        $this->assertSame(
            $summary['total_active'],
            $summary['total_reported'] + $summary['total_unreported'],
            'Jumlah reported + unreported harus tepat sama dengan total_active'
        );
    }
}
