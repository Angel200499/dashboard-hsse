<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ReportingRateEmptyPeriodTest extends TestCase
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

    public function test_dashboard_fungsi_empty_period_reporting_rate_returns_no_sipeka_and_null_data(): void
    {
        $adminOp = $this->getOrCreateUser('test_admin_op', 'Admin Function', 'Operation');

        // Request tahun 2025 dengan bulan 5 (tidak ada data SIPEKA/manpower 2025)
        $response = $this->actingAs($adminOp)->get(route('dashboard.fungsi.data', [
            'nama_fungsi' => 'Operation',
            'rr_year'     => 2025,
            'rr_month'    => 5,
        ]));

        $response->assertStatus(200);
        $json = $response->json();

        $this->assertSame('no_sipeka', $json['rrStatus']);
        $this->assertNull($json['charts']['reporting']['data']['Operation']);
    }

    public function test_dashboard_fungsi_all_months_mode_returns_zero_distribution(): void
    {
        $adminOp = $this->getOrCreateUser('test_admin_op', 'Admin Function', 'Operation');

        // Request tahun 2025 dengan semua bulan (rr_month = "")
        $response = $this->actingAs($adminOp)->get(route('dashboard.fungsi.data', [
            'nama_fungsi' => 'Operation',
            'rr_year'     => 2025,
            'rr_month'    => '',
        ]));

        $response->assertStatus(200);
        $json = $response->json();

        $this->assertSame('all_months', $json['rrStatus']);
        $this->assertSame('distribusi', $json['charts']['reporting']['mode']);
        $this->assertEquals(0, $json['charts']['reporting']['data']['Operation']);
    }

    public function test_dashboard_global_empty_period_reporting_rate_returns_null_data_and_status(): void
    {
        $adminHsse = $this->getOrCreateUser('test_admin_hsse', 'Admin HSSE', null);

        $response = $this->actingAs($adminHsse)->get(route('dashboard', [
            'rr_year'  => 2025,
            'rr_month' => 5,
        ]), ['X-Requested-With' => 'XMLHttpRequest']);

        $response->assertStatus(200);
        $json = $response->json();

        $this->assertSame('no_sipeka', $json['rrStatus']);
        $this->assertSame('manpower_rasio', $json['charts']['reporting']['mode']);
        foreach ($json['charts']['reporting']['data'] as $fungsi => $val) {
            $this->assertNull($val, "Fungsi {$fungsi} harus null pada periode tanpa data");
        }
    }

    public function test_html_render_contains_both_canvas_and_empty_container(): void
    {
        $adminOp = $this->getOrCreateUser('test_admin_op', 'Admin Function', 'Operation');

        // Render HTML dengan periode kosong
        $response = $this->actingAs($adminOp)->get(route('dashboard.fungsi', [
            'nama_fungsi' => 'Operation',
            'rr_year'     => 2025,
            'rr_month'    => 5,
        ]));

        $response->assertStatus(200);
        $content = $response->getContent();

        // Canvas chart2 dan container chart2_empty harus selalu ada di DOM
        $this->assertStringContainsString('id="chart2"', $content);
        $this->assertStringContainsString('id="chart2_empty"', $content);
        $this->assertStringContainsString('id="chart2-title"', $content);

        // Pada initial render periode kosong, chart2 harus memiliki class hidden dan chart2_empty tidak tersembunyi
        $this->assertMatchesRegularExpression('/<canvas id="chart2"[^>]*class="[^"]*hidden[^"]*"/s', $content);
        $this->assertDoesNotMatchRegularExpression('/<div id="chart2_empty"[^>]*class="[^"]*hidden[^"]*"/s', $content);
    }
}
