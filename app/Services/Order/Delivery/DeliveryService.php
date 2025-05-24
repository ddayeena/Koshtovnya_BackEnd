<?php

namespace App\Services\Order\Delivery;

use App\Models\ProductDescription;
use App\Services\ExchangeRateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DeliveryService
{
    private string $apiKey;
    private string $cityCender;
    private NovaPoshtaService $novaPoshtaService; 
    private $exchange_rate_service;

    public function __construct(NovaPoshtaService $novaPoshtaService, ExchangeRateService $exchange_rate_service)
    {
        $this->novaPoshtaService = $novaPoshtaService;
        $this->exchange_rate_service = $exchange_rate_service;
        $this->apiKey = env('NOVAPOSHTA_API_KEY');
        $this->cityCender = env('NOVAPOSHTA_CITY_SENDER');
    }

    public function calculateCost(string $cityRecipient, array $productIds, string $serviceType, Request $request): array
    {
        $products = ProductDescription::whereIn('id', $productIds)->get();

        // Calculate total weight in kg
        $totalWeightInKg = $products->sum('weight') / 1000;

        // API request to Nova Poshta
        $response = Http::post('https://api.novaposhta.ua/v2.0/json/', [
            'apiKey' => $this->apiKey,
            'modelName' => 'InternetDocument',
            'calledMethod' => 'getDocumentPrice',
            'methodProperties' => [
                'CitySender' => $this->cityCender,
                'CityRecipient' => $cityRecipient,
                'Weight' => $totalWeightInKg,
                'ServiceType' => $serviceType,
                'CargoType' => 'Parcel',
            ],
        ]);
        $result = $response->json();

        ['currency' => $currency, 'rate' => $rate] = $this->exchange_rate_service->resolveCurrencyData($request);

        //Return data
        if (isset($result['success']) && $result['success']) {
            return [
                'success' => true,
                'data' => [
                    'cost' => round($result['data'][0]['Cost'] / $rate, 2),
                    'currency' => $currency,
                ],
            ];
        }

        return [
            'success' => false,
            'message' => $result['errors'] ?? 'Error calculating delivery cost.',
        ];
    }

    public function filterCitiesByDeliveryType($cities, $deliveryType, $query)
    {
        //Filter citis by their delivery type
        switch ($deliveryType) {
            case 'Самовивіз з Нової Пошти':
                return $this->novaPoshtaService->filterByQuery(
                    array_filter($cities, fn($city) => $city['Delivery1'] === '1' || $city['Delivery4'] === '1'),
                    $query
                );
            case 'Самовивіз з поштоматів Нової Пошти':
                return $this->novaPoshtaService->filterByQuery(
                    array_filter($cities, fn($city) => $city['Delivery3'] === '1'),
                    $query
                );
            case 'Кур\'єр Нової Пошти':
                return $this->novaPoshtaService->filterByQuery(
                    array_filter($cities, fn($city) => $city['Delivery2'] === '1'),
                    $query
                );
            default: return [];
        }
    }
    
    public function getFilteredWarehouses($cityRef, $deliveryType, $warehouseName)
    {
        // Get warehouses of the city
        $warehouses = collect($this->novaPoshtaService->getWarehouses($cityRef));

        // Filter warehouses by their delivery type
        if ($deliveryType === 'Самовивіз з Нової Пошти') {
            $warehouses = $warehouses->filter(function ($warehouse) {
                return $warehouse['CategoryOfWarehouse'] !== 'Postomat';
            });
        } elseif ($deliveryType === 'Самовивіз з поштоматів Нової Пошти') {
            $warehouses = $warehouses->filter(function ($warehouse) {
                return $warehouse['CategoryOfWarehouse'] === 'Postomat';
            });
        }

        // Further filter warehouses by name query
        return $this->novaPoshtaService->filterByQuery($warehouses->toArray(), $warehouseName);
    }
    
}
