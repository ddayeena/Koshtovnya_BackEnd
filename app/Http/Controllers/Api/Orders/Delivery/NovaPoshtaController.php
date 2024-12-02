<?php

namespace App\Http\Controllers\Api\Orders\Delivery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Delivery\NovaPoshta\CityResource;
use App\Http\Resources\Delivery\NovaPoshta\WarehouseResource;
use App\Services\Order\Delivery\NovaPoshtaService;
use Illuminate\Http\Request;

class NovaPoshtaController extends Controller
{
    private NovaPoshtaService $novaPoshtaService;

    public function __construct(NovaPoshtaService $novaPoshtaService)
    {
        $this->novaPoshtaService = $novaPoshtaService;
    }

    public function getCities(Request $request)
    {
        $query = $request->input('city');
        $cities = $this->novaPoshtaService->getCities();

        // Filter cities that contain the entered part in the name 
        $filteredCities = $this->novaPoshtaService->filterByQuery($cities, $query);

        return response()->json([
            'success' => true,
            'data' => CityResource::collection($filteredCities)
        ]);
    }

    public function getWarehousesForCity(Request $request)
    {
        //Get data from request
        $cityName = $request->input('city');
        $warehouseName = $request->input('warehouse');

        // Check if a parameter for searching for a city is passed
        if (empty($cityName)) {
            return response()->json([
                'success' => false,
                'message' => 'City name parameter is required.'
            ], 400);
        }

        $cities = $this->novaPoshtaService->getCities();

        // Search this city to check if it exists
        $city = collect($cities)->firstWhere(function ($city) use ($cityName) {
            return strcasecmp($city['Description'], $cityName) === 0;  
        });
        if (!$city) {
            return response()->json(['success' => false, 'message' => 'City not found.'], 404);
        }

        // Get warehouses of this city
        $warehouses = $this->novaPoshtaService->getWarehouses($city['Ref']);
        // Filter warehouses that contain the entered part in the name 
        $filteredWareHouses = $this->novaPoshtaService->filterByQuery($warehouses, $warehouseName);

        return response()->json([
            'success' => true,
            'data' => WarehouseResource::collection($filteredWareHouses)
        ]);
    }
}
