<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSiteSettingsRequest;
use App\Http\Resources\SiteSettingResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\SiteSetting;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
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

        $orders_count = Order::where('status', 'Доставлено')
            ->whereBetween('updated_at', [$start, $end])
            ->count();

        $sold_products_count = DB::table('order_product')
            ->join('orders', 'order_product.order_id', '=', 'orders.id')
            ->where('orders.status', 'Доставлено')
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
        }
         elseif ($diffInDays <= 90) {
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
    
    
}
