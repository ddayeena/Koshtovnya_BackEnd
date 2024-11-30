<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Product;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function store(Request $request)
    {
        //Check if the product exists
        $product = Product::findOrFail($request->product_id);
        
        //Check if the notification already exists
        $existingNotification = $request->user()->notifications()->where('product_id', $request->product_id)->first();
        if ($existingNotification) {
            return response()->json(['message' => 'You are already subscribed for notifications for this product.'], 400);
        }

        //Create notification
        $notification = Notification::create([
            'user_id' => $request->user()->id ?? null,
            'product_id' => $request->product_id,
        ]);

        return response()->json([
            'message' => 'You will be notified when the product is available!',
            'notification' => $notification,
        ]);
    }
}
