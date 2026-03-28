<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\AuditLog;
use App\Events\SecurityAlert;
use App\Events\CriticalSystemError;

class ClamavUploadTest extends TestCase
{
    use RefreshDatabase;

    // EICAR standard anti-virus test file signature
    const EICAR = 'X5O!P%@AP[4\PZX54(P^)7CC)7}$EICAR-STANDARD-ANTIVIRUS-TEST-FILE!$H+H*';

    protected function setUp(): void
    {
        parent::setUp();
        // Assuming there is an admin user
        if (User::where('user_type', 'admin')->count() === 0) {
            User::factory()->create(['user_type' => 'admin']);
        }
    }

    public function test_upload_with_eicar_virus_is_blocked_and_alerts_sent()
    {
        Event::fake([SecurityAlert::class]);
        
        // Mock the scanner since local/CI may not have actual ClamAV running on 3310
        $this->mock(\App\Services\ClamavService::class, function ($mock) {
            $mock->shouldReceive('scan')->once()->andReturn(false);
        });

        $user = User::factory()->create(['user_type' => 'seller']);
        $this->actingAs($user);

        // Turn on ClamAV in the environment for the test
        putenv('DISABLE_CLAMAV=false');

        $this->withoutExceptionHandling();
        $file = UploadedFile::fake()->createWithContent('eicar.txt', self::EICAR);

        $response = $this->postJson('/aiz-uploader/upload', [
            'aiz_file' => $file
        ]);

        $response->assertStatus(403)
                 ->assertJsonPath('status', 'error')
                 ->assertJsonPath('message', translate('Infected file detected. Upload rejected.'));

        Event::assertDispatched(SecurityAlert::class, function ($event) {
            return str_contains($event->message, 'Malware Blocked');
        });

        $this->assertDatabaseHas('audit_logs', [
            'action_type' => 'MALWARE_BLOCKED',
        ]);
    }

    public function test_clamav_fail_open_behavior_on_connection_error()
    {
        Event::fake([CriticalSystemError::class]);
        
        $user = User::factory()->create(['user_type' => 'seller']);
        $this->actingAs($user);

        // Force ClamAV to try a broken port to simulate daemon outage
        putenv('DISABLE_CLAMAV=false');
        putenv('CLAMAV_PORT=9999'); 

        $this->withoutExceptionHandling();
        $file = UploadedFile::fake()->createWithContent('safe.txt', 'Just some safe text');

        // It should FAIL-OPEN and return success ({}) since the connection will be refused
        $response = $this->postJson('/aiz-uploader/upload', [
            'aiz_file' => $file
        ]);

        $response->assertStatus(200);

        // Ensure the CriticalSystemError was fired to alert the admin
        Event::assertDispatched(CriticalSystemError::class, function ($event) {
            return $event->component === 'ClamAV Scanner';
        });

        // Ensure the frontend maintenance cache was set
        $this->assertTrue(Cache::has('system_degraded'));
        $this->assertEquals('Malware Scanner (ClamAV)', Cache::get('system_degraded')['component']);
    }
}
