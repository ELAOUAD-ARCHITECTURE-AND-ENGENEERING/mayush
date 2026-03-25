<?php

namespace Tests\Unit\OTP;

use Tests\TestCase;
use App\Services\SendSmsService;
use App\Models\OtpConfiguration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class SmsGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Setup default OTP config
        $this->config = new OtpConfiguration();
        $this->config->type = 'twilio';
        $this->config->value = 1;
        // Since OtpConfiguration doesn't have a factory yet, we create it manually or mock it
        // Better to seed it in DB since SendSmsService queries it
        OtpConfiguration::insert([
            'type' => 'twillo',
            'value' => 1,
            'info' => '[]',
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /** @test */
    public function send_sms_service_delegates_to_twillo_adapter()
    {
        $to = '+1234567890';
        $from = 'MAYUSH';
        $text = 'Your OTP is 1234';
        $templateId = 'temp_1';

        // Mock the Twillo adapter
        $twilloMock = Mockery::mock('overload:App\Services\OTP\Twillo');
        $twilloMock->shouldReceive('send')
            ->once()
            ->with($to, $from, $text, $templateId)
            ->andReturn(true);

        $service = new SendSmsService();
        $service->sendSMS($to, $from, $text, $templateId);

        // Mockery expectation handles verification
        $this->assertTrue(true);
    }

    /** @test */
    public function send_sms_service_delegates_to_fast2sms_adapter()
    {
        // Update config to fast2sms
        OtpConfiguration::where('value', 1)->update(['type' => 'fast2sms']);

        $to = '9876543210';
        $from = 'MAYUSH';
        $text = 'Your OTP is 5678';
        $templateId = 'temp_2';

        $fastMock = Mockery::mock('overload:App\Services\OTP\Fast2sms');
        $fastMock->shouldReceive('send')
            ->once()
            ->with($to, $from, $text, $templateId)
            ->andReturn(JSON_encode(['status' => 'success']));

        $service = new SendSmsService();
        $service->sendSMS($to, $from, $text, $templateId);

        $this->assertTrue(true);
    }

    /** @test */
    public function it_returns_null_if_adapter_class_does_not_exist()
    {
        OtpConfiguration::where('value', 1)->update(['type' => 'non_existent_provider']);

        $service = new SendSmsService();
        $result = $service->sendSMS('123', '456', 'test', 'id');

        $this->assertNull($result);
    }
}
