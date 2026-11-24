<?php

namespace Tests\Feature;

use App\Models\SipekaFinding;
use App\Models\User;
use Tests\TestCase;

class KeterlibatanObservasiDefaultPeriodTest extends TestCase
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

    public function test_default_period_uses_latest_data_year_and_max_month(): void
    {
        $adminOp = $this->getOrCreateUser('test_admin_op', 'Admin Function', 'Operation');

        $latestYear = SipekaFinding::maxTahun();
        $this->assertNotNull($latestYear, 'Database should have SipekaFinding data');

        $maxMonthOp = SipekaFinding::maxBulanTahunFungsi($latestYear, 'Operation');

        $response = $this->actingAs($adminOp)->get(route('dashboard.fungsi.data', [
            'nama_fungsi' => 'Operation',
        ]));

        $response->assertStatus(200);
        $json = $response->json();

        // Default koYear and rrYear should match latest data year
        $this->assertSame($latestYear, $json['koYear']);
        $this->assertSame($latestYear, $json['rrYear']);

        // Default koMonth should match max month for that year and function
        if ($maxMonthOp !== null) {
            $this->assertSame($maxMonthOp, $json['koMonth']);
        }
    }

    public function test_manual_filter_can_override_default_period(): void
    {
        $adminOp = $this->getOrCreateUser('test_admin_op', 'Admin Function', 'Operation');

        $response = $this->actingAs($adminOp)->get(route('dashboard.fungsi.data', [
            'nama_fungsi' => 'Operation',
            'ko_year'     => 2025,
            'ko_month'    => 5,
        ]));

        $response->assertStatus(200);
        $json = $response->json();

        $this->assertSame(2025, $json['koYear']);
        $this->assertSame(5, $json['koMonth']);
    }

    public function test_html_render_has_selected_default_period_on_dashboard_fungsi(): void
    {
        $adminOp = $this->getOrCreateUser('test_admin_op', 'Admin Function', 'Operation');
        $latestYear = SipekaFinding::maxTahun();
        $maxMonthOp = SipekaFinding::maxBulanTahunFungsi($latestYear, 'Operation');

        // Tab 1: Dashboard Fungsi
        $response = $this->actingAs($adminOp)->get(route('dashboard.fungsi', ['fungsi' => 'Operation']));
        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('<option value="' . $latestYear . '" selected>' . $latestYear . '</option>', $content);
        $this->assertStringContainsString('<option value="' . $maxMonthOp . '" selected>', $content);

        // Tab 2: Dashboard Global di dalam Dashboard Fungsi
        $responseGlobalTab = $this->actingAs($adminOp)->get(route('dashboard.fungsi', ['fungsi' => 'Operation', 'tab' => 'global']));
        $responseGlobalTab->assertStatus(200);
        $contentGlobalTab = $responseGlobalTab->getContent();

        $this->assertStringContainsString('<option value="' . $latestYear . '" selected>' . $latestYear . '</option>', $contentGlobalTab);
    }

    public function test_html_render_has_selected_default_period_on_dashboard_global(): void
    {
        $adminHsse = $this->getOrCreateUser('test_admin_hsse', 'Admin HSSE', null);
        $latestYear = SipekaFinding::maxTahun();
        $maxMonthGlobal = SipekaFinding::maxBulanTahun($latestYear);

        $response = $this->actingAs($adminHsse)->get(route('dashboard'));
        $response->assertStatus(200);
        $content = $response->getContent();

        // ko_year & ko_month default to latest data
        $this->assertStringContainsString('<option value="' . $latestYear . '" selected>' . $latestYear . '</option>', $content);
        $this->assertStringContainsString('<option value="' . $maxMonthGlobal . '" selected>', $content);

        // Form must have onsubmit="return false;" to prevent page reload
        $this->assertStringContainsString('onsubmit="return false;"', $content);
    }

    public function test_dashboard_global_ajax_filter_returns_json_and_preserves_state(): void
    {
        $adminHsse = $this->getOrCreateUser('test_admin_hsse', 'Admin HSSE', null);
        $latestYear = SipekaFinding::maxTahun();
        $maxMonthGlobal = SipekaFinding::maxBulanTahun($latestYear);

        // 1. Initial AJAX request
        $response = $this->actingAs($adminHsse)->getJson(route('dashboard'));
        $response->assertStatus(200);
        $json = $response->json();

        $this->assertTrue($json['success']);
        $this->assertSame($latestYear, $json['koYear']);
        $this->assertSame($maxMonthGlobal, $json['koMonth']);
        $this->assertArrayHasKey('kpi', $json);
        $this->assertArrayHasKey('charts', $json);

        // 2. Filter override via AJAX
        $responseFilter = $this->actingAs($adminHsse)->getJson(route('dashboard', [
            'ko_year'  => 2025,
            'ko_month' => 4,
        ]));
        $responseFilter->assertStatus(200);
        $jsonFilter = $responseFilter->json();

        $this->assertTrue($jsonFilter['success']);
        $this->assertSame(2025, $jsonFilter['koYear']);
        $this->assertSame(4, $jsonFilter['koMonth']);

        // 3. User chooses "Semua Bulan"
        $responseAllMonths = $this->actingAs($adminHsse)->getJson(route('dashboard', [
            'ko_year'  => 2025,
            'ko_month' => '',
        ]));
        $responseAllMonths->assertStatus(200);
        $jsonAllMonths = $responseAllMonths->json();

        $this->assertTrue($jsonAllMonths['success']);
        $this->assertNull($jsonAllMonths['koMonth']);
    }
}
