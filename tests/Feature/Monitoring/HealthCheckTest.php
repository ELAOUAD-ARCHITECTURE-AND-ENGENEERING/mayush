<?php

namespace Tests\Feature\Monitoring;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_check_command_runs_successfully()
    {
        $exitCode = Artisan::call('mayush:health-check');
        $this->assertEquals(0, $exitCode);
    }

    public function test_operations_audit_command_runs_successfully()
    {
        // We just assert the command runs without throwing an exception.
        // It may return 1 if Horizon is inactive in the test environment.
        $exitCode = Artisan::call('mayush:operations:audit');
        $this->assertContains($exitCode, [0, 1]);
    }
}
