<?php

namespace App\Http\Controllers\Api\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\OrderRequest;
use App\Http\Resources\Delivery\DeliveryResource;
use App\Http\Resources\Order\OrderListResource;
use App\Http\Resources\Order\OrderProductResource;
use App\Http\Resources\Order\OrderResource;
use App\Mail\OrderCancelledMail;
use App\Mail\OrderDeliveredMail;
use App\Mail\OrderShippedMail;
use App\Models\Order;
use App\Models\User;
use App\Services\ExchangeRateService;
use App\Services\Order\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OrderController extends Controller
{
    protected $orderService;
    private $exchange_rate_service;


    public function __construct(OrderService $orderService, ExchangeRateService $exchange_rate_service)
    {
        $this->orderService = $orderService;
        $this->exchange_rate_service = $exchange_rate_service;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $locale = request('lang', app()->getLocale());
        App::setLocale($locale);

        $query = Order::query();

        $sortBy = $request->get('sort_by');
        $sortOrder = $request->get('sort_order', 'asc');

        $sortFieldsMap = [
            'id' => 'orders.id',
            'order_date' => 'orders.created_at',
            'status' => 'orders.status',
            'phone_number' => 'orders.phone_number',
            'products' => 'products_names',
        ];

        if ($sortBy === 'products') {
            $query->leftJoin('order_product', 'orders.id', '=', 'order_product.order_id')
                ->leftJoin('products', 'order_product.product_id', '=', 'products.id')
                ->select('orders.*', DB::raw('GROUP_CONCAT(products.name ORDER BY products.name ASC SEPARATOR ", ") as products_names'))
                ->groupBy('orders.id');
        }

        if (isset($sortFieldsMap[$sortBy]) && in_array($sortOrder, ['asc', 'desc'])) {
            $query->orderBy($sortFieldsMap[$sortBy], $sortOrder);
        } else {
            $query->orderBy('orders.id', 'desc');
        }

        $orders = $query->paginate(10);

        return OrderListResource::collection($orders);
    }

    public function userOrders(Request $request)
    {
        $locale = request('lang', app()->getLocale());
        App::setLocale($locale);

        //Get orders for authenticated user
        $orders = $request->user()->orders()->with('products')->get()->reverse();

        ['currency' => $currency, 'rate' => $rate] = $this->exchange_rate_service->resolveCurrencyData($request);
        OrderResource::setCurrency($currency, $rate);
        OrderProductResource::setCurrency($currency, $rate);
        return response()->json([
            'message' => 'Orders retrieved successfully.',
            'orders' => OrderResource::collection($orders)
        ]);
    }
    public function adminOrders(Request $request, Int $id)
    {
        //Get orders
        $user = User::findOrFail($id);
        $orders = $user->orders()->with('products')->get();

        ['currency' => $currency, 'rate' => $rate] = $this->exchange_rate_service->resolveCurrencyData($request);
        OrderResource::setCurrency($currency, $rate);
        return response()->json([
            'message' => 'Orders retrieved successfully.',
            'orders' => OrderResource::collection($orders)
        ]);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(OrderRequest $request)
    {
        $data = $request->validated();

        $currencyData = $this->exchange_rate_service->resolveCurrencyData($request);

        $data['currency'] = $currencyData['currency'];
        $data['rate'] = $currencyData['rate'];

        try {
            // Process order
            $result = $this->orderService->processOrder($data, $request->user());

            //Return result
            return response()->json([
                'message' => 'Order created successfully!',
                'data' => [
                    'order' => OrderResource::make($result['order']),
                    'delivery' => DeliveryResource::make($result['delivery']),
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create order.',
                'errors' => json_decode($e->getMessage(), true) ?? $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id, Request $request)
    {
        $locale = request('lang', app()->getLocale());
        App::setLocale($locale);

        ['currency' => $currency, 'rate' => $rate] = $this->exchange_rate_service->resolveCurrencyData($request);

        if ($request->isAdminPanel) {
            $order = Order::with('products')
                ->where('id', $id)
                ->firstOrFail();
        } else {
            $order = Order::with('products')
                ->where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();
        }
        OrderResource::setCurrency($currency, $rate);
        OrderProductResource::setCurrency($currency, $rate);
        DeliveryResource::setCurrency($currency, $rate);

        return response()->json([
            'data' => [
                'order' => OrderResource::make($order),
                'total_cost' => number_format($order->total_amount / $rate, 2, '.', ''),
                'currency' => $currency,
                'delivery' => DeliveryResource::make($order->delivery),
                'payment_method' => __('payment.method.' . $order->payment->payment_method),
                'status' => __('payment.status.' . $order->payment->status),
            ]
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $locale = request('lang', app()->getLocale());
        App::setLocale($locale);

        $order = Order::findOrFail($id);

        $data = $request->validate([
            'status' => 'required|in:Відправлено,Доставлено,Скасовано,Canceled,Delivered,Sent'
        ]);

        $enStatuses = trans('orders.status', [], 'en');
        $translatedStatuses = array_flip($enStatuses);

        if (isset($translatedStatuses[$data['status']])) {
            $data['status'] = $translatedStatuses[$data['status']];
        }


        if (in_array($order->status, ['Скасовано', 'Доставлено']) && $data['status'] !== $order->status) {
            return response()->json([
                'message' => 'You cannot change the status after cancelling or delivering order.'
            ], 400);
        }

        $order->update(['status' => $data['status']]);

        $email = $order->user->email ?? null;

        if ($email) {
            if ($data['status'] === 'Відправлено') {
                Mail::to($email)->send(new OrderShippedMail($order));
            } elseif ($data['status'] === 'Доставлено') {
                if ($order->payment->payment_method === 'Післяоплата') {
                    $order->payment->update([
                        'status' => 'Оплачено',
                        'paid_at' => now(),
                    ]);
                }
                Mail::to($email)->send(new OrderDeliveredMail($order, $order->delivery));
            } elseif ($data['status'] === 'Скасовано') {
                Mail::to($email)->send(new OrderCancelledMail($order));
            }
        }

        return response()->json([
            'message' => 'Orders status updated successfully',
            'order' => OrderListResource::make($order)
        ], 200);
    }



    public function cancel(string $id)
    {
        $order = Order::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($order->status !== 'В очікуванні') {
            return response()->json([
                'message' => 'The order cannot be canceled because it is already being processed or delivered.'
            ], 400);
        }

        $order->update(['status' => 'Скасовано']);

        Mail::to($order->user->email)->send(new OrderCancelledMail($order));

        return response()->json([
            'message' => 'Order successfully canceled.',
            'order' => $order
        ], 200);
    }
}
