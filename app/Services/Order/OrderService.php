<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\Delivery;
use App\Models\DeliveryType;
use App\Models\Payment;
use App\Models\UserAddress;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function processOrder(array $data, $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $cart = $user->cart;

            // Check cart to see if all products are available to order
            $errors = $this->validateCart($cart);
            if (!empty($errors)) {
                throw new \Exception(json_encode([
                    'message' => 'Some products are not available in the required quantity.',
                    'errors' => $errors,
                ]));
            }
            $totalAmount = $data['delivery_cost'] + $data['cart_cost'];

            //Create order
            $order = $this->createOrder($data, $user, $totalAmount);

            // Attach products to order
            $this->attachProductsToOrder($order, $cart);

            // Remove products from cart
            $cart->products()->detach();

            //Create delivery
            $deliveryTypeId = DeliveryType::where('name', $data['delivery_name'])->value('id');
            if (!$deliveryTypeId) {
                throw new \Exception('Delivery type not found');
            }
            $delivery = $this->createDelivery($order, $data, $deliveryTypeId);

            //Create payment
            $payment = $this->createPayment($order, $data, $totalAmount);

            $this->createUserAddress($user, $data,  $deliveryTypeId);

            //Update products quantity in stock
            $this->updateProductStock($order);

            return compact('order', 'delivery', 'payment');
        });
    }
    
    //Create order
    private function createOrder(array $data, $user, $totalAmount)
    {
        return Order::create([
            'user_id' => $user->id,
            'first_name' => $data['first_name'],
            'second_name' => $data['second_name'],
            'last_name' => $data['last_name'],
            'phone_number' => $data['phone_number'],
            'total_amount' => $totalAmount,
            'status' => 'В очікуванні',
        ]);
    }

    // Attach products to order
    private function attachProductsToOrder($order, $cart)
    {
        foreach ($cart->products as $product) {
            $order->products()->attach($product->id, [
                'quantity' => $product->pivot->quantity,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    //Create delivery
    private function createDelivery($order, array $data, $deliveryTypeId)
    {
        return Delivery::create([
            'order_id' => $order->id,
            'city' => $data['city'],
            'delivery_type_id' => $deliveryTypeId,
            'delivery_address' => $data['delivery_address'],
            'cost' => $data['delivery_cost'],
        ]);
    }

    //Create payment
    private function createPayment($order, array $data, $totalAmount)
    {
        return Payment::create([
            'order_id' => $order->id,
            'type_of_card' => $data['type_of_card'],
            'payment_method' => $data['payment_method'],
            'amount' => $totalAmount,
        ]);
    }

        public function createUserAddress($user, $data, $delivery_type_id)
    {
        $userAddress = $user->userAddress;
        if (!$userAddress) {
            $userAddress = UserAddress::create([
                'user_id' => $user->id,
                'delivery_type_id' => $delivery_type_id,
                'city' => $data['city'],
                'delivery_address' => $data['delivery_address'],
            ]);
        }
        return $userAddress;
    }

    //Update products quantity in stock
    private function updateProductStock($order)
    {
        foreach ($order->products as $product) {
            $product->quantity -= $product->pivot->quantity;
            if ($product->quantity < 0) {
                throw new \Exception('Insufficient stock for product: ' . $product->name);
            }
            $product->save();
        }
    }

    //Validate cart products
    private function validateCart($cart)
    {
        $errors = [];

        if (!$cart || $cart->products->isEmpty()) {
            throw new \Exception('Cart is empty');
        }

        //Check cart to see if all products are available to order
        foreach ($cart->products as $product) {
            //If there are no products left at all
            if ($product->quantity === 0) {
                $errors[] = [
                    'message' => "Product is out of stock, remove it from your cart to place an order.",
                    'product_name' => $product->name,
                    'available_quantity' => 0,
                ];
                //if there are more cart product than there are in stock 
            } elseif ($product->quantity < $product->pivot->quantity) {
                $errors[] = [
                    'message' => "Only {$product->quantity} products left in stock, remove it from your cart or change the quantity to place an order.",
                    'product_name' => $product->name,
                    'available_quantity' => $product->quantity,
                ];
            }
        }

        return $errors;
    }
}
