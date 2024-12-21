<?php

namespace App\Services\Order;

use App\Mail\OrderDetailsMail;
use App\Models\Order;
use App\Models\Delivery;
use App\Models\DeliveryType;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\UserAddress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

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

            Mail::to($user->email)->send(new OrderDetailsMail($order, $delivery, $payment));

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
                'size' => $product->pivot->size,
                'quantity' => $product->pivot->quantity,
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
        // Перевірка наявності продуктів у замовленні
        if (empty($order->products)) {
            throw new \Exception('Order has no products.');
        }
    
        foreach ($order->products as $product) {
            // Перевірка, чи існує size у pivot
            if (!isset($product->pivot->size) || !isset($product->pivot->quantity)) {
                throw new \Exception('Size or quantity data is missing for product: ' . $product->name);
            }
    
            $size = $product->pivot->size;
            $quantity = $product->pivot->quantity;
    
            // Знайти відповідний запис у таблиці product_variants
            $productVariant = ProductVariant::where('product_id', $product->id)
                ->where('size', $size)
                ->first();
    
            if (!$productVariant) {
                throw new \Exception('Product variant not found for product: ' . $product->name . ' with size: ' . $size);
            }
    
            // Зменшити кількість у таблиці product_variants
            $productVariant->quantity -= $quantity;
    
            // Перевірка на недостатню кількість
            if ($productVariant->quantity < 0) {
                throw new \Exception('Insufficient stock for product: ' . $product->name . ' with size: ' . $size);
            }
    
            // Зберегти оновлену кількість
            $productVariant->save();
        }
    }

    //Validate cart products
    private function validateCart($cart)
    {
        $errors = [];
        
        if (!$cart || $cart->products->isEmpty()) {
            throw new \Exception('Cart is empty');
        }
    
        // Check cart to see if all products with specific sizes are available to order
        foreach ($cart->products as $product) {
            $size = $product->pivot->size; 
            $variant = $product->productVariants()->where('size', $size)->first(); 
    
            if (!$variant) {
                $errors[] = [
                    'message' => "Product with size {$size} is not available.",
                    'product_name' => $product->name,
                    'selected_size' => $size,
                ];
            } elseif ($variant->quantity === 0) {
                $errors[] = [
                    'message' => "Product with size {$size} is out of stock, remove it from your cart to place an order.",
                    'product_name' => $product->name,
                    'selected_size' => $size,
                    'available_quantity' => 0,
                ];
            } elseif ($variant->quantity < $product->pivot->quantity) {
                $errors[] = [
                    'message' => "Only {$variant->quantity} products with size {$size} are left in stock, remove it from your cart or change the quantity to place an order.",
                    'product_name' => $product->name,
                    'selected_size' => $size,
                    'available_quantity' => $variant->quantity,
                ];
            }
        }
    
        return $errors;
    }
    
}
