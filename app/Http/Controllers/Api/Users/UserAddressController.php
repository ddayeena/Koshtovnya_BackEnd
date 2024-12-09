<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserAddressRequest;
use App\Http\Resources\UserAddressResource;
use App\Models\DeliveryType;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\Request;

class UserAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserAddressRequest $request)
    {
        //User can have only one address
        if ($request->user()->userAddress) {
            return response()->json(['message' => 'User already has address.'], 404);
        }

        $data = $request->validated();

        //Get delivery_type_id
        $delivery_type_id = DeliveryType::where('name', $data['delivery_name'])->value('id');
        if (!$delivery_type_id) {
            return response()->json(['message' => 'Delivery type not found'], 404);
        }

        $userAddress = UserAddress::create([
            'user_id' => $request->user()->id,
            'delivery_type_id' =>  $delivery_type_id,
            'city' => $data['city'],
            'delivery_address' => $data['delivery_address'],
        ]);

        $this->updateUserPhoneNumber($request->user(), $data['phone_number']);

        return response()->json([
            'message' => 'User address added successfully.',
            'data' => [
                'user_address' => UserAddressResource::make($userAddress)
            ]
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        $userAddress = $request->user()->userAddress;

        //User can have only one address
        if (!$userAddress) {
            return response()->json([
                'data' => null,
                'message' => 'User didn`t add address yet.'
            ], 404);
        }

        return response()->json([
            'data' => [
                'address' => UserAddressResource::make($userAddress)
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserAddressRequest $request, string $id)
    {
        $data = $request->validated();
        
        // Check if this id belongs to authenticated user and get his address
        $userAddress = UserAddress::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        //Get delivery_type_id
        $delivery_type_id = DeliveryType::where('name', $data['delivery_name'])->value('id');
        if (!$delivery_type_id) {
            return response()->json(['message' => 'Delivery type not found'], 404);
        }

        $data['delivery_type_id'] = $delivery_type_id;
        // Unset delivery_name so that it is not updated
        unset($data['delivery_name']);

        $this->updateUserPhoneNumber($request->user(), $data['phone_number']);
        // Unset phone_number so that it is not updated
        unset($data['phone_number']);

        // Update data
        $userAddress->update($data);

        return response()->json([
            'message' => 'User address updated successfully.',
            'data' => [
                'user_address' => UserAddressResource::make($userAddress),
            ],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Check if this id belongs to authenticated user and get his address
        $userAddress = UserAddress::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $userAddress->delete();
        return response()->json(['message' => 'User address deleted successfilly'], 200);
    }

    // Get user`s phone number
    public function getUserPhoneNumber(Request $request)
    {
        $user = $request->user();
        return response()->json([
            'phone_number' => $user->phone_number,
        ]);
    }

    // Update user phone number
    private function updateUserPhoneNumber($user, $phoneNumber)
    {
        if (!empty($phoneNumber)) {
            $user->update(['phone_number' => $phoneNumber]);
        }
    }
}
