<?php

namespace App\Http\Controllers\Api\Orders\Delivery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Delivery\DeliveryTypeResource;
use App\Models\DeliveryType;

class DeliveryTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //Get delivery types
        $pickup = DeliveryType::where('type','pickup')->get();
        $courier = DeliveryType::where('type', 'courier')->get();
        //Return data
        return response()->json([
            'data' => [
                'pickup' => DeliveryTypeResource::collection($pickup),
                'courier' => DeliveryTypeResource::collection($courier),
            ]
        ]);
    }

    
}
