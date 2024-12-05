<?php

namespace App\Services\Order\Delivery;

use App\Models\ProductDescription;
use Illuminate\Support\Facades\Http;

class DeliveryService
{
    private string $apiKey;
    private string $cityCender;
    private NovaPoshtaService $novaPoshtaService; 

    public function __construct(NovaPoshtaService $novaPoshtaService)
    {
        $this->novaPoshtaService = $novaPoshtaService;
        $this->apiKey = env('NOVAPOSHTA_API_KEY');
        $this->cityCender = env('NOVAPOSHTA_CITY_SENDER');
    }

    public function calculateCost(string $cityRecipient, array $productIds, string $serviceType): array
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

        //Return data
        if (isset($result['success']) && $result['success']) {
            return [
                'success' => true,
                'data' => [
                    'cost' => $result['data'][0]['Cost'],
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
