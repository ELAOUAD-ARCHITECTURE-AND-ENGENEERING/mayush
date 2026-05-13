<?php

namespace Tests\Feature\Payment;

use Tests\TestCase;

class CmiGatewayDiagnosticsTest extends TestCase
{
    public function test_cmi_diagnose_reports_missing_required_configuration(): void
    {
        config([
            'cmi.merchant_id' => null,
            'cmi.secret_key' => null,
            'cmi.gateway_url' => null,
            'cmi.allowed_ips' => [],
        ]);

        $this->artisan('cmi:diagnose')
            ->expectsOutputToContain('Blocker: CMI_MERCHANT_ID is missing.')
            ->expectsOutputToContain('Blocker: CMI_SECRET_KEY is missing.')
            ->expectsOutputToContain('Blocker: CMI_GATEWAY_URL is missing.')
            ->assertExitCode(1);
    }

    public function test_cmi_diagnose_treats_empty_ip_allowlist_as_production_blocker(): void
    {
        config([
            'cmi.merchant_id' => 'merchant',
            'cmi.secret_key' => 'secret',
            'cmi.gateway_url' => 'https://attijari.cmi.co.ma/fim/est3Dgate',
            'cmi.callback_url' => 'https://mayush.example/cmi/callback',
            'cmi.ok_url' => 'https://mayush.example/cmi/success',
            'cmi.fail_url' => 'https://mayush.example/cmi/fail',
            'cmi.allowed_ips' => [],
        ]);

        $this->artisan('cmi:diagnose', ['--production' => true])
            ->expectsOutputToContain('Callback IP whitelist middleware: registered')
            ->expectsOutputToContain('Blocker: CMI_ALLOWED_IPS is empty.')
            ->assertExitCode(1);
    }

    public function test_cmi_diagnose_passes_for_production_safe_configuration(): void
    {
        config([
            'cmi.merchant_id' => 'merchant',
            'cmi.secret_key' => 'secret',
            'cmi.gateway_url' => 'https://attijari.cmi.co.ma/fim/est3Dgate',
            'cmi.callback_url' => 'https://mayush.example/cmi/callback',
            'cmi.ok_url' => 'https://mayush.example/cmi/success',
            'cmi.fail_url' => 'https://mayush.example/cmi/fail',
            'cmi.allowed_ips' => ['196.12.225.1', '196.12.225.2'],
        ]);

        $this->artisan('cmi:diagnose', ['--production' => true])
            ->expectsOutputToContain('Merchant ID: configured')
            ->expectsOutputToContain('Callback route methods: POST')
            ->expectsOutputToContain('Callback IP whitelist middleware: registered')
            ->expectsOutputToContain('Allowed callback IPs: 2 configured')
            ->expectsOutputToContain('CMI diagnosis completed without local blockers.')
            ->assertExitCode(0);
    }
}
