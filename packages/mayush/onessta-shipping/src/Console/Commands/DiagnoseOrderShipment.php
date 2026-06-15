<?php

namespace Mayush\Shipping\Onessta\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mayush\Shipping\Onessta\Models\OnesstaCityMap;
use Mayush\Shipping\Onessta\Models\OnesstaShipment;
use Mayush\Shipping\Onessta\Services\OrderShipmentDispatchService;

class DiagnoseOrderShipment extends Command
{
    protected $signature = 'onessta:diagnose-order
        {order : Local order ID or order code}
        {--dispatch : Queue shipment creation through the normal ONESSTA order dispatch service}
        {--force : Force dispatch even when a local ONESSTA shipment already exists}';

    protected $description = 'Diagnose why a local order did or did not create an ONESSTA shipment.';

    public function handle(OrderShipmentDispatchService $dispatchService): int
    {
        $order = $this->findOrder((string) $this->argument('order'));

        if (!$order) {
            $this->error('Order not found.');

            return self::FAILURE;
        }

        $this->info("ONESSTA order diagnosis for order #{$order->id} ({$order->code})");
        $this->line('');

        $blockers = $this->writeReadinessChecks($order);

        if ($this->option('dispatch')) {
            $this->line('');
            $this->info('Dispatch attempt');

            $result = $dispatchService->ensureForOrder($order, (bool) $this->option('force'));

            $this->line('Status: ' . ($result['status'] ?? 'unknown'));
            $this->line('Message: ' . ($result['message'] ?? 'No message returned.'));

            if (isset($result['queue_connection'])) {
                $this->line('Queue connection: ' . $result['queue_connection']);
            }

            if (isset($result['queue'])) {
                $this->line('Queue name: ' . $result['queue']);
            }

            if (isset($result['shipment_code'])) {
                $this->line('Shipment code: ' . ($result['shipment_code'] ?: 'pending remote creation'));
            }

            return in_array($result['status'] ?? null, ['queued', 'created', 'exists'], true)
                ? self::SUCCESS
                : self::FAILURE;
        }

        if ($blockers > 0) {
            $this->warn("Diagnosis completed with {$blockers} blocker(s). Re-run with --dispatch only after fixing them.");

            return self::FAILURE;
        }

        $this->info('Diagnosis completed without local blockers. Use --dispatch to queue shipment creation for this order.');

        return self::SUCCESS;
    }

    private function findOrder(string $identifier): ?Order
    {
        return Order::query()
            ->with(['user', 'orderDetails'])
            ->when(is_numeric($identifier), fn ($query) => $query->where('id', (int) $identifier))
            ->when(!is_numeric($identifier), fn ($query) => $query->where('code', $identifier))
            ->first();
    }

    private function writeReadinessChecks(Order $order): int
    {
        $blockers = 0;

        $checks = [
            'ONESSTA integration' => config('onessta.enabled', false) ? 'enabled' : 'disabled',
            'ONESSTA addon' => addon_is_activated('onessta') ? 'active' : 'inactive',
            'Order delivery status' => (string) ($order->delivery_status ?? 'unknown'),
            'Order shipping type' => (string) ($order->shipping_type ?? 'not set'),
            'Order shipping method' => (string) ($order->shipping_method ?? 'not set'),
            'Queue connection' => (string) config('onessta.queue.create_shipment_connection', config('queue.default')),
            'Queue name' => (string) config('onessta.queue.name', 'onessta'),
        ];

        foreach ($checks as $label => $value) {
            $this->line("{$label}: {$value}");
        }

        if (!config('onessta.enabled', false)) {
            $blockers++;
            $this->warn('Blocker: set ONESSTA_ENABLED=true.');
        }

        if (!addon_is_activated('onessta')) {
            $blockers++;
            $this->warn('Blocker: activate the ONESSTA addon.');
        }

        if (!$this->hasCredentials()) {
            $blockers++;
            $this->warn('Blocker: configure ONESSTA_TOKEN, ONESSTA_API_KEY, and ONESSTA_CLIENT_ID.');
        }

        if (!$this->isEligibleShippingMethod($order)) {
            $blockers++;
            $this->warn('Blocker: order shipping method/type is not ONESSTA or home_delivery.');
        }

        if (($order->delivery_status ?? null) === 'cancelled') {
            $blockers++;
            $this->warn('Blocker: cancelled orders are not dispatched.');
        }

        $this->writeShipmentStatus($order);
        $blockers += $this->writeCityMappingStatus($order);
        $this->writeFailedJobHint($order);

        return $blockers;
    }

    private function hasCredentials(): bool
    {
        return filled(config('onessta.auth.token'))
            && filled(config('onessta.auth.api_key'))
            && filled(config('onessta.auth.client_id'));
    }

    private function isEligibleShippingMethod(Order $order): bool
    {
        $allowedMethods = ['onessta', 'home_delivery'];

        return in_array($order->shipping_method, $allowedMethods, true)
            || in_array($order->shipping_type, $allowedMethods, true);
    }

    private function writeShipmentStatus(Order $order): void
    {
        $shipment = OnesstaShipment::query()->where('order_id', $order->id)->latest('id')->first();

        if (!$shipment) {
            $this->line('Local ONESSTA shipment: none');

            return;
        }

        $this->line('Local ONESSTA shipment: exists');
        $this->line('Shipment code: ' . ($shipment->code ?: 'not set'));
        $this->line('Shipment status: ' . ($shipment->status ?: 'not set'));
        $this->line('Remote external ID: ' . ($shipment->external_id ?: 'not set'));
    }

    private function writeCityMappingStatus(Order $order): int
    {
        $addressData = is_string($order->shipping_address)
            ? json_decode($order->shipping_address, true)
            : ($order->shipping_address ?? []);

        $cityId = $addressData['city_id'] ?? $order->city_id ?? null;

        if (!$cityId) {
            $this->warn('City mapping: no local city_id found on shipping address; job may fall back to city name if it maps remotely.');

            return 0;
        }

        $map = OnesstaCityMap::query()
            ->where('local_city_id', (int) $cityId)
            ->where('active', true)
            ->first();

        if (!$map) {
            $this->warn("Blocker: no active ONESSTA city mapping for local city_id {$cityId}.");

            return 1;
        }

        $this->line("City mapping: local {$cityId} -> ONESSTA {$map->remote_city_id} ({$map->remote_city_name})");

        return 0;
    }

    private function writeFailedJobHint(Order $order): void
    {
        if (!Schema::hasTable('failed_jobs')) {
            return;
        }

        $count = DB::table('failed_jobs')
            ->where('payload', 'like', '%CreateShipmentJob%')
            ->where('payload', 'like', '%' . $order->id . '%')
            ->count();

        if ($count > 0) {
            $this->warn("Failed jobs: {$count} possible failed ONESSTA shipment job(s) reference this order.");
        }
    }
}
