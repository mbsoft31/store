<?php
// app/Http/Controllers/Api/ReportController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\OrderItem;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function dashboard(Request $request)
    {
        $startDate = $request->start_date 
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->startOfMonth();
            
        $endDate = $request->end_date 
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfMonth();

        $tenantId = $request->user()->tenant_id;

        // Sales metrics
        $salesData = Order::where('tenant_id', $tenantId)
            ->where('status', OrderStatus::PAID)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('
                SUM(total_cents) as total_revenue_cents,
                COUNT(*) as total_orders,
                AVG(total_cents) as avg_order_value_cents,
                SUM(CASE WHEN channel = "pos" THEN total_cents ELSE 0 END) as pos_revenue_cents,
                SUM(CASE WHEN channel = "online" THEN total_cents ELSE 0 END) as online_revenue_cents
            ')
            ->first();

        // Top products
        $topProducts = OrderItem::select([
                'products.id as product_id',
                'products.name',
                DB::raw('SUM(order_items.quantity) as quantity_sold'),
                DB::raw('SUM(order_items.quantity * order_items.unit_price_cents) as revenue_cents')
            ])
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.tenant_id', $tenantId)
            ->where('orders.status', OrderStatus::PAID)
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('revenue_cents')
            ->limit(5)
            ->get();

        // Low stock count
        $lowStockCount = Product::where('tenant_id', $tenantId)
            ->with(['inventoryMovements'])
            ->get()
            ->filter(fn($product) => $product->isLowStock())
            ->count();

        // Recent orders
        $recentOrders = Order::where('tenant_id', $tenantId)
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'total_cents' => $order->total_cents,
                    'channel' => $order->channel->value,
                    'created_at' => $order->created_at,
                ];
            });

        return response()->json([
            'period' => [
                'start_date' => $startDate->toISOString(),
                'end_date' => $endDate->toISOString(),
            ],
            'sales' => [
                'total_revenue_cents' => (int) $salesData->total_revenue_cents,
                'total_orders' => (int) $salesData->total_orders,
                'avg_order_value_cents' => (int) $salesData->avg_order_value_cents,
                'pos_revenue_cents' => (int) $salesData->pos_revenue_cents,
                'online_revenue_cents' => (int) $salesData->online_revenue_cents,
            ],
            'top_products' => $topProducts,
            'low_stock_count' => $lowStockCount,
            'recent_orders' => $recentOrders,
        ]);
    }
}
