<?php

namespace App\Services\Order\Delivery;

use App\Models\Order;
use Illuminate\Support\Facades\Http;

class NovaPoshtaService
{
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = env('NOVAPOSHTA_API_KEY');
    }

    // Sends a POST request to Nova Poshta API and returns the data
    public function sendRequest(string $modelName, string $method, array $methodProperties = []): array
    {
        $response = Http::post('https://api.novaposhta.ua/v2.0/json/', [
            'apiKey' => $this->apiKey,
            'modelName' => $modelName,
            'calledMethod' => $method,
            'methodProperties' => (object)$methodProperties,
        ]);

        // Throws an exception if the request is unsuccessful
        if (!$response->successful()) {
            throw new \Exception("Error while fetching data from Nova Poshta API.");
        }

        // Returns the data part of the response, or an empty array if not found
        return $response->json()['data'] ?? [];
    }

    //Get all cities
    public function getCities(): array
    {
        return $this->sendRequest('Address', 'getCities');
    }

    // Get warehouses for a specific city
    public function getWarehouses(string $cityRef, int $limit = 1000): array
    {
        return $this->sendRequest('Address', 'getWarehouses', ['CityRef' => $cityRef, 'Limit' => $limit]);
    }

    //Get streets of some city
    public function getStreetsList($cityRef)
    {
        return $this->sendRequest('Address', 'getStreet', ['CityRef' => $cityRef]);
    }

    // Filters items by the query, checking if the query exists in the description
    public function filterByQuery(array $items, ?string $query): array
    {
        if ($query === null) return $items;

        // Search for exact matches
        $exactMatches = array_filter($items, function ($item) use ($query) {
            return stripos($item['Description'], $query) !== false && $item['Description'] === $query;
        });
        if (!empty($exactMatches)) return $exactMatches;

        // If there are no exact matches, search for everything that contains the query as part of it
        $partialMatches = array_filter($items, function ($item) use ($query) {
            return stripos($item['Description'], $query) !== false;
        });

        return $partialMatches;
    }    
}
