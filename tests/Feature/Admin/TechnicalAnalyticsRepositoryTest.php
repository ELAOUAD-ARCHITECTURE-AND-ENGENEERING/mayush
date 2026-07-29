<?php

namespace Tests\Feature\Admin;

use App\Models\Shop;
use App\Models\User;
use App\Repositories\Analytics\TechnicalAnalyticsRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TechnicalAnalyticsRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_kpis_and_growth_count_only_authoritatively_approved_shops(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-06 12:00:00'));

        $approvedOld = $this->shop([
            'verification_status' => 1,
            'registration_approval' => 1,
            'approval_status' => 'approved',
            'created_at' => now()->subMonth(),
            'updated_at' => now()->subMonth(),
        ]);
        $approvedCurrent = $this->shop([
            'verification_status' => 1,
            'registration_approval' => 1,
            'approval_status' => 'approved',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);
        $this->shop([
            'verification_status' => 1,
            'registration_approval' => 0,
            'approval_status' => 'pending',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);
        $this->shop([
            'verification_status' => 0,
            'registration_approval' => 1,
            'approval_status' => 'approved',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $repository = new TechnicalAnalyticsRepository();
        $kpis = $repository->getVendorKpis(now()->startOfMonth(), now());
        $growth = $repository->getVendorGrowthChart();
        $currentMonth = collect($growth)->last();

        $this->assertSame(3, $kpis['active']);
        $this->assertSame(2, $kpis['new']);
        $this->assertSame(3, $currentMonth['active']);
        $this->assertSame(2, $currentMonth['new']);
        $this->assertDatabaseHas('shops', ['id' => $approvedOld->id]);
        $this->assertDatabaseHas('shops', ['id' => $approvedCurrent->id]);
    }

    public function test_security_metrics_respect_selected_date_range(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-06 12:00:00'));

        DB::table('audit_logs')->insert([
            'action_type' => 'FAILED_LOGIN',
            'description' => 'Failed login attempt for email: admin@example.com',
            'ip_address' => '127.0.0.1',
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ]);
        DB::table('audit_logs')->insert([
            'action_type' => 'FAILED_LOGIN',
            'description' => 'Old failed login',
            'ip_address' => '127.0.0.2',
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ]);
        DB::table('audit_logs')->insert([
            'action_type' => 'MALWARE_BLOCKED',
            'description' => 'Infected file rejected: malware.pdf',
            'ip_address' => '127.0.0.3',
            'created_at' => now()->subMinutes(30),
            'updated_at' => now()->subMinutes(30),
        ]);

        $metrics = (new TechnicalAnalyticsRepository())
            ->getSecurityMetrics(now()->startOfDay(), now());

        $this->assertSame(1, $metrics['failed_logins']);
        $this->assertSame(1, $metrics['blocked_uploads']);
        $this->assertCount(2, $metrics['recent_events']);
        $this->assertSame('MALWARE_BLOCKED', $metrics['recent_events'][0]['action_type']);
        $this->assertSame('FAILED_LOGIN', $metrics['recent_events'][1]['action_type']);
        $this->assertSame('Fichier infecté rejeté : malware.pdf', $metrics['recent_events'][0]['description']);
        $this->assertSame('Échec de connexion pour l’e-mail : admin@example.com', $metrics['recent_events'][1]['description']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function shop(array $attributes): Shop
    {
        return Shop::factory()->create(array_merge([
            'user_id' => User::factory()->seller()->create()->id,
        ], $attributes));
    }
}
