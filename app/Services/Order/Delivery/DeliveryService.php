<?php

namespace App\Services\Order\Delivery;

use App\Models\ProductDescription;
use Illuminate\Support\Facades\Http;

class DeliveryService
{
    private string $apiKey;
    private string $cityCender;

    public function __construct()
    {
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
    
    
}
