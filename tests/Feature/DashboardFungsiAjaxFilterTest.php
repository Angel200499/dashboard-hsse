<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class DashboardFungsiAjaxFilterTest extends TestCase
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

    public function test_ajax_data_endpoint_returns_json_structure(): void
    {
        $adminOp = $this->getOrCreateUser('test_admin_op', 'Admin Function', 'Operation');

        $response = $this->actingAs($adminOp)->get(route('dashboard.fungsi.data', [
            'nama_fungsi' => 'Operation',
            'year'        => 2026,
        ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'activeTab',
            'fungsi',
            'currentColor',
            'fungsiSapPct',
            'kpi' => ['total', 'open', 'in_progress', 'closed'],
            'charts' => [
                'fungsi',
                'reporting',
                'kategori',
                'keterlibatan',
                'persentase_fungsi',
                'tindak_lanjut',
                'unsafe_action',
                'unsafe_condition',
            ],
            'globalCharts',
            'globalKpi',
            'selectedYear',
            'selectedMonth',
            'koYear',
            'koMonth',
            'rrYear',
            'rrMonth',
        ]);

        $this->assertTrue($response->json('success'));
        $this->assertSame('Operation', $response->json('fungsi'));
        $this->assertSame(2026, $response->json('selectedYear'));
    }

    public function test_standard_route_with_wants_json_header_returns_json(): void
    {
        $adminOp = $this->getOrCreateUser('test_admin_op', 'Admin Function', 'Operation');

        $response = $this->actingAs($adminOp)->getJson(route('dashboard.fungsi', [
            'nama_fungsi' => 'Operation',
            'year'        => 2025,
        ]));

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertSame(2025, $response->json('selectedYear'));
    }

    public function test_ajax_data_supports_year_and_month_filter(): void
    {
        $adminOp = $this->getOrCreateUser('test_admin_op', 'Admin Function', 'Operation');

        $response = $this->actingAs($adminOp)->get(route('dashboard.fungsi.data', [
            'nama_fungsi' => 'Operation',
            'year'        => 2026,
            'month'       => 8,
        ]));

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertSame(2026, $response->json('selectedYear'));
        $this->assertSame(8, $response->json('selectedMonth'));
    }

    public function test_function_user_cannot_access_other_function_data(): void
    {
        $adminOp = $this->getOrCreateUser('test_admin_op', 'Admin Function', 'Operation');

        $response = $this->actingAs($adminOp)->get(route('dashboard.fungsi.data', [
            'nama_fungsi' => 'Maintenance',
        ]));

        $response->assertStatus(403);
    }

    public function test_ajax_data_supports_global_tab(): void
    {
        $adminOp = $this->getOrCreateUser('test_admin_op', 'Admin Function', 'Operation');

        $response = $this->actingAs($adminOp)->get(route('dashboard.fungsi.data', [
            'nama_fungsi' => 'Operation',
            'tab'         => 'global',
            'g_year'      => 2026,
            'g_month'     => 8,
        ]));

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertSame('global', $response->json('activeTab'));
        $this->assertSame(2026, $response->json('gYear'));
        $this->assertSame(8, $response->json('gMonth'));
        $this->assertNotNull($response->json('globalCharts'));
        $this->assertNotNull($response->json('globalKpi'));
    }
}
