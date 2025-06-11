<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Order\OrderListResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductStatsResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Services\ExchangeRateService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    private $exchange_rate_service;

    public function __construct(ExchangeRateService $exchange_rate_service)
    {
        $this->exchange_rate_service = $exchange_rate_service;
    }

    public function getSummary(Request $request)
    {
        $data = $request->validate([
            'start_date' => 'nullable|date|before_or_equal:end_date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'period' => 'nullable|in:day,week,month,year',
        ]);

        if (!empty($data['start_date']) && !empty($data['end_date'])) {
            $start = Carbon::parse($data['start_date'])->startOfDay();
            $end = Carbon::parse($data['end_date'])->endOfDay();
        } elseif (!empty($data['period'])) {
            $now = Carbon::now();

            switch ($data['period']) {
                case 'day':
                    $start = $now->copy()->subDay();
                    $end = $now;
                    break;
                case 'week':
                    $start = $now->copy()->subWeek();
                    $end = $now;
                    break;
                case 'month':
                    $start = $now->copy()->subMonth();
                    $end = $now;
                    break;
                case 'year':
                    $start = $now->copy()->subYear();
                    $end = $now;
                    break;
            }
        } else {
            return response()->json(['message' => 'Вкажіть або період, або початкову і кінцеву дату.'], 422);
        }

        $orders_count = Order::whereBetween('updated_at', [$start, $end])
            ->count();

        $sold_products_count = DB::table('order_product')
            ->join('orders', 'order_product.order_id', '=', 'orders.id')
            ->whereBetween('orders.updated_at', [$start, $end])
            ->sum('order_product.quantity');

        $users_count = User::whereBetween('created_at', [$start, $end])
            ->where('role', 'user')
            ->count();

        $reviews_count = Review::whereBetween('created_at', [$start, $end])
            ->count();

        return response()->json([
            'orders_count' => $orders_count,
            'sold_products_count' => (int)$sold_products_count,
            'reviews_count' => $reviews_count,
            'users_count' => $users_count,
            'start' => $start->toDateTimeString(),
            'end' => $end->toDateTimeString(),
        ]);
    }

    public function orderDynamics(Request $request)
    {
        $locale = request('lang', app()->getLocale());
        Carbon::setLocale($locale); 
        
        $data = $request->validate([
            'start_date' => 'nullable|date|before_or_equal:end_date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'period' => 'nullable|in:day,week,month,year',
        ]);

        $now = Carbon::now();
        $period = $data['period'] ?? null;

        if (!empty($data['start_date']) && !empty($data['end_date'])) {
            $start = Carbon::parse($data['start_date'])->startOfDay();
            $end = Carbon::parse($data['end_date'])->endOfDay();
        } elseif ($period) {
            switch ($period) {
                case 'day':
                    $start = $now->copy()->subHours(23)->startOfHour();
                    $end = $now->copy()->endOfHour();
                    break;
                case 'week':
                    $start = $now->copy()->subDays(6)->startOfDay();
                    $end = $now->endOfDay();
                    break;
                case 'month':
                    $start = $now->copy()->subDays(29)->startOfDay();
                    $end = $now->endOfDay();
                    break;
                case 'year':
                    $start = $now->copy()->subYear()->addDay()->startOfDay();
                    $end = $now->endOfDay();
                    break;
                default:
                    return response()->json(['message' => 'Неправильний період.'], 422);
            }
        } else {
            return response()->json(['message' => 'Вкажіть або період, або дату початку і кінця.'], 422);
        }

        $diffInHours = $start->diffInHours($end);
        $diffInDays = $start->diffInDays($end);
        $type = '';

        $labels = [];
        $values = [];

        if ($diffInHours <= 24) {
            // Групування по годинах з урахуванням дати
            $type = 'hour';
            $hour = $start->copy();
            while ($hour <= $end) {
                $labels[] = $hour->format('Y-m-d H:00:00'); // повна дата і година
                $values[] = 0;
                $hour->addHour();
            }

            $orders = DB::table('orders')
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m-%d %H:00:00") as hour_label, COUNT(*) as total')
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('hour_label')
                ->get();

            $labelIndexMap = array_flip($labels);

            foreach ($orders as $order) {
                if (isset($labelIndexMap[$order->hour_label])) {
                    $values[$labelIndexMap[$order->hour_label]] = (int)$order->total;
                }
            }

            // Перетворимо мітки назад у формат H:i для відображення на графіку
            $labels = array_map(fn($l) => Carbon::parse($l)->format('H:i'), $labels);
        } elseif ($diffInDays <= 90) {
            // Групування по днях
            $type = 'day';
            $date = $start->copy();
            while ($date <= $end) {
                $labels[] = $date->format('d.m');
                $values[] = 0;
                $date->addDay();
            }

            $orders = DB::table('orders')
                ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('date')
                ->get();

            foreach ($orders as $order) {
                $orderDate = Carbon::parse($order->date)->startOfDay();
                $index = $start->diffInDays($orderDate);
                if ($index >= 0 && $index < count($values)) {
                    $values[$index] = (int)$order->total;
                }
            }
        } else {
            // Групування по місяцях
            $type = 'month';
            $date = $start->copy()->startOfMonth();
            while ($date <= $end) {
                $labels[] = $date->translatedFormat('M Y');
                $values[] = 0;
                $date->addMonth();
            }

            $orders = DB::table('orders')
                ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as total')
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('month')
                ->get();

            foreach ($orders as $order) {
                $date = Carbon::createFromFormat('Y-m', $order->month)->startOfMonth();
                $index = $start->copy()->startOfMonth()->diffInMonths($date);
                if ($index >= 0 && $index < count($values)) {
                    $values[$index] = (int)$order->total;
                }
            }
        }

        return response()->json([
            'labels' => $labels,
            'values' => $values,
            'start' => $start->toDateTimeString(),
            'end' => $end->toDateTimeString(),
            'type' => $type,
        ]);
    }

    public function latestOrders()
    {
        $locale = request('lang', app()->getLocale());
        App::setLocale($locale);

        $orders = Order::where('status', 'В очікуванні')
            ->latest()
            ->take(5)
            ->get();
        return OrderListResource::collection($orders);
    }

    public function popularProducts(Request $request)
    {
        $locale = request('lang', app()->getLocale());
        App::setLocale($locale);

        $data = $request->validate([
            'start_date' => 'nullable|date|before_or_equal:end_date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'period' => 'nullable|in:day,week,month,year',
        ]);

        if (!empty($data['start_date']) && !empty($data['end_date'])) {
            $start = Carbon::parse($data['start_date'])->startOfDay();
            $end = Carbon::parse($data['end_date'])->endOfDay();
        } elseif (!empty($data['period'])) {
            $now = Carbon::now();

            switch ($data['period']) {
                case 'day':
                    $start = $now->copy()->subDay();
                    break;
                case 'week':
                    $start = $now->copy()->subWeek();
                    break;
                case 'month':
                    $start = $now->copy()->subMonth();
                    break;
                case 'year':
                    $start = $now->copy()->subYear();
                    break;
            }

            $end = $now;
        } else {
            return response()->json(['message' => 'Вкажіть або період, або початкову і кінцеву дату.'], 422);
        }

        // Популярні товари за період (кількість замовлень у вказаному діапазоні)
        $products = Product::with('productDescription')
            ->withCount(['orders as orders_count' => function ($query) use ($start, $end) {
                $query->whereBetween('orders.created_at', [$start, $end]);
            }])
            ->having('orders_count', '>', 0)
            ->orderBy('orders_count', 'desc')
            ->take(6)
            ->get();


        $products->loadCount('reviews')
            ->loadAvg('reviews', 'rating');

        ['currency' => $currency, 'rate' => $rate] = $this->exchange_rate_service->resolveCurrencyData($request);
        ProductStatsResource::setCurrency($currency, $rate);

        return response()->json([
            'start' => $start->toDateTimeString(),
            'end' => $end->toDateTimeString(),
            'products' => ProductStatsResource::collection($products),
        ]);
    }

    public function income(Request $request)
    {
        // 1. Отримати валюту і курс
        ['currency' => $currency, 'rate' => $rate] = $this->exchange_rate_service->resolveCurrencyData($request);

        // Валідація параметрів
        $data = $request->validate([
            'start_date' => 'nullable|date|before_or_equal:end_date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'period' => 'nullable|in:day,week,month,year',
        ]);

        // Визначення діапазону дат
        if (!empty($data['start_date']) && !empty($data['end_date'])) {
            $start = Carbon::parse($data['start_date'])->startOfDay();
            $end = Carbon::parse($data['end_date'])->endOfDay();
        } elseif (!empty($data['period'])) {
            $now = Carbon::now();

            switch ($data['period']) {
                case 'day':
                    $start = $now->copy()->subDay();
                    $end = $now;
                    break;
                case 'week':
                    $start = $now->copy()->subWeek();
                    $end = $now;
                    break;
                case 'month':
                    $start = $now->copy()->subMonth();
                    $end = $now;
                    break;
                case 'year':
                    $start = $now->copy()->subYear();
                    $end = $now;
                    break;
            }
        } else {
            return response()->json(['message' => 'Вкажіть або період, або початкову і кінцеву дату.'], 422);
        }

        // Отримання замовлень
        $orders = Order::whereHas('payment', fn($q) => $q->where('status', 'Оплачено'))
            ->with([
                'products.productDescription.beadProducer',
                'products.fittings',
                'payment'
            ])
            ->whereBetween('created_at', [$start, $end])
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        // Перетворення замовлень
        $data = $orders->map(function ($order) use ($currency, $rate) {
            $totalExpenses = 0;
            $totalAmount = $order->total_amount;

            foreach ($order->products as $product) {
                $productQuantity = $product->pivot->quantity;

                $weight = $product->productDescription->weight ?? 0;
                $costPerGram = $product->productDescription->beadProducer->cost_per_gram ?? 0;
                $beadCost = $weight * $costPerGram * $productQuantity;

                $fittingCost = 0;
                foreach ($product->fittings as $fitting) {
                    $fittingQuantity = $fitting->pivot->quantity ?? 0;
                    $costPerUnit = $fitting->cost_per_unit ?? 0;
                    $fittingCost += $fittingQuantity * $costPerUnit;
                }

                $totalExpenses += ($beadCost + $fittingCost);
            }

            $netIncome = $totalAmount - $totalExpenses;

            return [
                'id' => $order->id,
                'date' => $order->created_at->toDateString(),
                'revenue' =>  app()->getLocale() === 'en' ? 'Sale of goods' : 'Продаж товару',
                'transaction_number' => $order->payment->transaction_number,
                'total_amount' => round($totalAmount / $rate, 2),
                'expenses' => round($totalExpenses / $rate, 2),
                'net_income' => round($netIncome / $rate, 2),
            ];
        });

        // Підрахунок загальних значень
        $totalIncome = $data->sum('total_amount');
        $totalExpenses = $data->sum('expenses');
        $totalNetIncome = $data->sum('net_income');

        return response()->json([
            'data' => $data,
            'summary' => [
                'total_income' => round($totalIncome, 2),
                'total_expenses' => round($totalExpenses, 2),
                'total_net_income' => round($totalNetIncome, 2),
            ],
            'currency' => $currency,
            'start' => $start->toDateTimeString(),
            'end' => $end->toDateTimeString(),
        ]);
    }
}
