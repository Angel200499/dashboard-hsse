<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\DashboardChartService;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardFungsiGlobalTabTest extends TestCase
{
    protected function getOrCreateUser(string $username, string $role, ?string $fungsi = null): User
    {
        return User::updateOrCreate(
            ['username' => $username],
            [
                'name'      => ucwords(str_replace('_', ' ', $username)),
                'email'     => "{$username}@example.com",
                'password'  => Hash::make('password123'),
                'role'      => $role,
                'fungsi'    => $fungsi,
                'is_active' => true,
            ]
        );
    }

    /**
     * 1. Tab navigasi muncul di Dashboard Fungsi untuk seluruh fungsi.
     */
    public function test_tab_navigation_rendered_for_all_functions(): void
    {
        $users = [
            $this->getOrCreateUser('tab_test_op', 'Admin Function', 'Operation'),
            $this->getOrCreateUser('tab_test_mt', 'Admin Function', 'Maintenance'),
            $this->getOrCreateUser('tab_test_bs', 'Admin Function', 'Business Support'),
            $this->getOrCreateUser('tab_test_hsse', 'Admin HSSE', 'HSSE'),
        ];

        foreach ($users as $user) {
            $response = $this->actingAs($user)->get(route('dashboard.fungsi', $user->fungsi));
            $response->assertStatus(200);
            $response->assertSee('Dashboard ' . $user->fungsi);
            $response->assertSee('Dashboard Global');
            $response->assertSee('tab=global');
        }
    }

    /**
     * 2. Default tab adalah Dashboard Fungsi (Tab 1) yang mempertahankan seluruh fitur existing.
     */
    public function test_default_tab_is_function_dashboard(): void
    {
        $adminOp = $this->getOrCreateUser('tab_test_op', 'Admin Function', 'Operation');

        $response = $this->actingAs($adminOp)->get(route('dashboard.fungsi', 'Operation'));
        $response->assertStatus(200);

        // Header & KPI Fungsi
        $response->assertSee('Dashboard Fungsi: Operation');
        $response->assertSee('Total Temuan');
        $response->assertSee('Daftar Temuan Fungsi Operation');

        // Chart Function ada
        $response->assertSee('id="chart1"', false);
        $response->assertSee('id="chart2"', false);
        $response->assertSee('id="chart3"', false);
        $response->assertSee('id="chart4"', false);
        $response->assertSee('id="chart5"', false);
        $response->assertSee('id="chart6"', false);
        $response->assertSee('id="chart7"', false);
        $response->assertSee('id="chart8"', false);

        // Chart Global tidak ada di Tab 1
        $response->assertDontSee('id="g_chartTrending"', false);
        $response->assertDontSee('Total Temuan (Semua Fungsi)');
    }

    /**
     * 3. Tab 2 (Dashboard Global) menampilkan seluruh grafik global dan metrik agregat.
     */
    public function test_global_tab_displays_global_charts_and_aggregated_kpi(): void
    {
        $adminOp = $this->getOrCreateUser('tab_test_op', 'Admin Function', 'Operation');

        $response = $this->actingAs($adminOp)->get(route('dashboard.fungsi', ['nama_fungsi' => 'Operation', 'tab' => 'global']));
        $response->assertStatus(200);

        // Subtitle & KPI Global
        $response->assertSee('Dashboard Global (Area Lahendong)');
        $response->assertSee('Total Temuan (Semua Fungsi)');
        $response->assertSee('Status Open (Semua Fungsi)');
        $response->assertSee('Status Closed (Semua Fungsi)');

        // Seluruh canvas global 8 chart + Trending ada di Tab 2
        $response->assertSee('id="g_chart1"', false);
        $response->assertSee('id="g_chart2"', false);
        $response->assertSee('id="g_chartTrending"', false);
        $response->assertSee('id="g_chart3"', false);
        $response->assertSee('id="g_chart4"', false);
        $response->assertSee('id="g_chart5"', false);
        $response->assertSee('id="g_chart6"', false);
        $response->assertSee('id="g_chart7"', false);
        $response->assertSee('id="g_chart8"', false);

        // Closing rate card & summary text untuk Trending
        $response->assertSee('id="g_closing-rate-card"', false);
        $response->assertSee('id="g_closing-rate-value"', false);
        $response->assertSee('id="g_trending-summary"', false);

        // Read-only: Tabel temuan tidak ditampilkan di Tab Global
        $response->assertDontSee('Daftar Temuan Fungsi Operation');
    }

    /**
     * 4. Filter independen Tab Global berfungsi dan mempertahankan tab=global.
     */
    public function test_global_tab_independent_filters(): void
    {
        $adminOp = $this->getOrCreateUser('tab_test_op', 'Admin Function', 'Operation');

        $response = $this->actingAs($adminOp)->get(route('dashboard.fungsi', [
            'nama_fungsi'  => 'Operation',
            'tab'          => 'global',
            'g_year'       => 2026,
            'g_rr_year'    => 2026,
            'g_rr_month'   => 1,
            'g_peka_year'  => 2026,
            'g_ko_year'    => 2026,
            'g_ko_month'   => 1,
        ]));

        $response->assertStatus(200);
        $response->assertSee('tab=global');
        $response->assertSee('Reset Filter Global');
    }

    /**
     * 5. Data Global konsisten dengan DashboardChartService::getCharts(null).
     */
    public function test_global_tab_data_consistency(): void
    {
        $adminOp = $this->getOrCreateUser('tab_test_op', 'Admin Function', 'Operation');

        $chartService = app(DashboardChartService::class);
        $expectedGlobal = $chartService->getCharts(null, 2026, null, 2026);

        $response = $this->actingAs($adminOp)->get(route('dashboard.fungsi', [
            'nama_fungsi' => 'Operation',
            'tab'         => 'global',
            'g_year'      => 2026,
            'g_peka_year' => 2026,
        ]));

        $response->assertStatus(200);

        // Ambil variable view
        $viewGlobalCharts = $response->viewData('globalCharts');
        $this->assertNotNull($viewGlobalCharts);
        $this->assertEquals(
            array_keys($expectedGlobal['fungsi']),
            array_keys($viewGlobalCharts['fungsi'])
        );
        $this->assertEquals(
            $expectedGlobal['fungsi'],
            $viewGlobalCharts['fungsi']
        );
    }
}

