<?php

namespace App\Http\Controllers\Api\Orders\Delivery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Delivery\NovaPoshta\CityResource;
use App\Http\Resources\Delivery\NovaPoshta\StreetResource;
use App\Http\Resources\Delivery\NovaPoshta\WarehouseResource;
use App\Services\Order\Delivery\DeliveryService;
use App\Services\Order\Delivery\NovaPoshtaService;
use Illuminate\Http\Request;

class NovaPoshtaController extends Controller
{
    private NovaPoshtaService $novaPoshtaService;
    private DeliveryService $deliveryService;

    public function __construct(NovaPoshtaService $novaPoshtaService, DeliveryService $deliveryService)
    {
        $this->novaPoshtaService = $novaPoshtaService;
        $this->deliveryService = $deliveryService;
    }

    public function getCities(Request $request)
    {
        //Get data from request
        $deliveryType = $request->input('delivery_type');
        $query = $request->input('city');
        $cities = $this->novaPoshtaService->getCities();

        // Filter cities by delivery type
        $filteredCities = $this->deliveryService->filterCitiesByDeliveryType($cities, $deliveryType, $query);

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
        $deliveryType = $request->input('delivery_type'); 

        // Check if a parameter is passed
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
        if (!$city) return response()->json(['success' => false, 'message' => 'City not found.'], 404);

        // Get filtered warehouses
        $filteredWarehouses = $this->deliveryService->getFilteredWarehouses($city['Ref'], $deliveryType, $warehouseName);

        return response()->json([
            'success' => true,
            'data' => WarehouseResource::collection($filteredWarehouses)
        ]);
    }

    public function getStreetsForCity(Request $request)
    {
        //Get data from request
        $cityName = $request->input('Ref');
        $street = $request->input('street');


        // Get streets list for this city
        $streets = $this->novaPoshtaService->getStreetsList($cityName);
        // Filter streets that contain the entered part in the name
        $filteredstreets = $this->novaPoshtaService->filterByQuery($streets, $street);

        return response()->json([
            'success' => true,
            'data' => StreetResource::collection($filteredstreets)
        ]);
    }

    public function calculateDeliveryCost(Request $request)
    {
        $validated = $request->validate([
            'CityRecipient' => 'required|string',
            'product_ids' => 'required|array',
            'ServiceType' => 'required|string|in:WarehouseDoors,WarehouseWarehouse',
        ]);

        //Calculate delivery cost
        $result = $this->deliveryService->calculateCost(
            $validated['CityRecipient'],
            $validated['product_ids'],
            $validated['ServiceType']
        );

        //Return data
        if ($result['success']) {
            return response()->json([
                'success' => true,
                'data' => $result['data'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 400);
    }
}
