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
            ->where('role','user')
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
    
}
