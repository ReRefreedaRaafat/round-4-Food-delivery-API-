<?php

namespace App\Http\Controllers\Api\Chef;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Chef;
use App\Models\Order;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function statistics(Request $request)
    {
        // $chef = Auth::user()->chef;
        $chef = Chef::first();

        if (!$chef) {
            return ApiResponse::unauthorized();
        }

        $ordersQuery = Order::whereHas('orderItems.dish', function ($q) use ($chef) {
            $q->where('chef_id', $chef->id);
        });

        $totalOrders = (clone $ordersQuery)->count();
        $runningOrders = (clone $ordersQuery)->whereIn('status', ['processing', 'on_the_way'])->count();
        $pendingOrders = (clone $ordersQuery)->where('status', 'pending')->count();
        $completedOrders = (clone $ordersQuery)->where('status', 'delivered')->count();
        $cancelledOrders = (clone $ordersQuery)->where('status', 'cancelled')->count();
        $revenue = (clone $ordersQuery)->where('status', 'delivered')->sum('total');

        return ApiResponse::success([ 
            'total_orders' => $totalOrders,
            'running_orders' => $runningOrders,
            'pending_orders' => $pendingOrders,
            'completed_orders' => $completedOrders,
            'cancelled_orders' => $cancelledOrders,
            'revenue' => $revenue,
        ]);
    }
} 