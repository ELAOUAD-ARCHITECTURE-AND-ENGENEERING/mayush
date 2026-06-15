<?php

namespace Mayush\Shipping\Onessta\Services;

use Illuminate\Support\Facades\Log;
use Mayush\Shipping\Onessta\Client\OnesstaClient;
use Mayush\Shipping\Onessta\DTOs\CityDto;
use Mayush\Shipping\Onessta\DTOs\PickupCityDto;

class CatalogService
{
    private OnesstaClient $client;

    public function __construct(OnesstaClient $client)
    {
        $this->client = $client;
    }

    public function listProducts(int $page = 1, int $limit = 20): array
    {
        if (!config('onessta.capabilities.products', true)) {
            throw new \RuntimeException('Product catalog is not supported by ONESSTA configuration');
        }

        $response = $this->client->get('/p/products', [
            'page' => $page,
            'limit' => $limit,
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Failed to fetch products: ' . $response->status());
        }

        return $response->json();
    }

    public function addProduct(array $productData): array
    {
        if (!config('onessta.capabilities.products', true)) {
            throw new \RuntimeException('Product catalog is not supported by ONESSTA configuration');
        }

        $response = $this->client->post('/p/products/add', $productData);

        if (!$response->successful()) {
            throw new \RuntimeException('Failed to add product: ' . $response->status());
        }

        Log::info('ONESSTA: Product added', ['sku' => $productData['sku'] ?? 'unknown']);

        return $response->json();
    }

    public function updateStock(int $remoteProductId, int $stock): bool
    {
        if (!config('onessta.capabilities.stock', true)) {
            throw new \RuntimeException('Stock updates are not supported by ONESSTA configuration');
        }

        $response = $this->client->post('/p/products/update_stock', [
            'id' => $remoteProductId,
            'stock' => $stock,
        ]);

        if (!$response->successful()) {
            Log::warning('ONESSTA: Stock update failed', [
                'product_id' => $remoteProductId,
                'stock' => $stock,
            ]);
            return false;
        }

        Log::info('ONESSTA: Stock updated', [
            'product_id' => $remoteProductId,
            'stock' => $stock,
        ]);

        return true;
    }
}
