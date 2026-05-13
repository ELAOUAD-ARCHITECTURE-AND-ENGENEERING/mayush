<?php

namespace App\Console\Commands;

use App\Http\Middleware\CmiIpWhitelist;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class DiagnoseCmiGateway extends Command
{
    protected $signature = 'cmi:diagnose
        {--production : Treat production safety warnings as blockers}
        {--allow-empty-ip-allowlist : Warn instead of failing when CMI_ALLOWED_IPS is empty in production}';

    protected $description = 'Diagnose local CMI gateway configuration, callback route wiring, and production safety settings.';

    public function handle(): int
    {
        $blockers = 0;
        $productionMode = $this->option('production') || app()->environment('production');
        $allowEmptyIpAllowlist = (bool) $this->option('allow-empty-ip-allowlist');

        $this->info('CMI gateway diagnosis');
        $this->line('Environment: ' . app()->environment());
        $this->line('Production checks: ' . ($productionMode ? 'enabled' : 'disabled'));
        $this->line('');

        $blockers += $this->checkRequiredConfig('Merchant ID', 'cmi.merchant_id', 'CMI_MERCHANT_ID');
        $blockers += $this->checkRequiredConfig('Secret key', 'cmi.secret_key', 'CMI_SECRET_KEY');
        $blockers += $this->checkRequiredConfig('Gateway URL', 'cmi.gateway_url', 'CMI_GATEWAY_URL');

        $callbackUrl = config('cmi.callback_url') ?: route('cmi.callback');
        $okUrl = config('cmi.ok_url') ?: route('cmi.success');
        $failUrl = config('cmi.fail_url') ?: route('cmi.fail');

        $this->line('Callback URL: ' . $callbackUrl);
        $this->line('Success URL: ' . $okUrl);
        $this->line('Fail URL: ' . $failUrl);

        if ($productionMode) {
            foreach ([
                'CMI_CALLBACK_URL' => $callbackUrl,
                'CMI_OK_URL' => $okUrl,
                'CMI_FAIL_URL' => $failUrl,
                'CMI_GATEWAY_URL' => config('cmi.gateway_url'),
            ] as $label => $url) {
                if (!Str::startsWith((string) $url, 'https://')) {
                    $blockers++;
                    $this->warn("Blocker: {$label} must use HTTPS in production.");
                }
            }
        }

        $blockers += $this->checkCallbackRoute();
        $blockers += $this->checkAllowedIps($productionMode, $allowEmptyIpAllowlist);

        if ($blockers > 0) {
            $this->error("CMI diagnosis completed with {$blockers} blocker(s).");

            return self::FAILURE;
        }

        $this->info('CMI diagnosis completed without local blockers.');

        return self::SUCCESS;
    }

    private function checkRequiredConfig(string $label, string $configKey, string $envKey): int
    {
        $value = config($configKey);

        if (filled($value)) {
            $this->line("{$label}: configured");

            return 0;
        }

        $this->warn("Blocker: {$envKey} is missing.");

        return 1;
    }

    private function checkCallbackRoute(): int
    {
        $route = Route::getRoutes()->getByName('cmi.callback');

        if (!$route) {
            $this->warn('Blocker: route cmi.callback is not registered.');

            return 1;
        }

        $methods = $route->methods();
        $middleware = $route->gatherMiddleware();

        $this->line('Callback route methods: ' . implode(',', $methods));
        $this->line('Callback route URI: ' . $route->uri());

        $blockers = 0;

        if (!in_array('POST', $methods, true)) {
            $blockers++;
            $this->warn('Blocker: cmi.callback must accept POST callbacks.');
        }

        if (!$this->hasMiddleware($middleware, CmiIpWhitelist::class)) {
            $blockers++;
            $this->warn('Blocker: cmi.callback is missing CmiIpWhitelist middleware.');
        } else {
            $this->line('Callback IP whitelist middleware: registered');
        }

        return $blockers;
    }

    private function hasMiddleware(array $middleware, string $class): bool
    {
        foreach ($middleware as $entry) {
            if ($entry === $class || str_contains((string) $entry, $class)) {
                return true;
            }
        }

        return false;
    }

    private function checkAllowedIps(bool $productionMode, bool $allowEmptyIpAllowlist): int
    {
        $allowedIps = array_values(array_filter(array_map('trim', config('cmi.allowed_ips', []))));

        if (!empty($allowedIps)) {
            $this->line('Allowed callback IPs: ' . count($allowedIps) . ' configured');

            return 0;
        }

        if ($productionMode) {
            if ($allowEmptyIpAllowlist) {
                $this->warn('Warning: CMI_ALLOWED_IPS is empty. Callback IP allowlist is explicitly ignored for this deployment.');

                return 0;
            }

            $this->warn('Blocker: CMI_ALLOWED_IPS is empty. Configure CMI callback source IPs before production launch.');

            return 1;
        }

        $this->line('Allowed callback IPs: not configured locally');

        return 0;
    }
}
