<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // ✅ Get Sales and Revenue Reports
    public function salesReport(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');
    
        $orderQuery = Order::query();
        $customerQuery = Customer::query();
    
        if ($from && $to) {
            $orderQuery->whereBetween('created_at', [$from, $to]);
            $customerQuery->whereHas('orders', function ($q) use ($from, $to) {
                $q->whereBetween('created_at', [$from, $to]);
            });
        }
    
        $totalRevenue = $orderQuery->sum('paid_amount');
    
        $totalSales = $orderQuery->sum(DB::raw('(
            SELECT SUM(price * quantity)
            FROM order_products
            WHERE order_products.order_id = orders.id
        )'));
    
        $totalDebt = $customerQuery->sum(DB::raw('(
            SELECT SUM(price * quantity)
            FROM order_products
            WHERE order_products.order_id IN (
                SELECT id FROM orders WHERE orders.customer_id = customers.id
                ' . ($from && $to ? "AND orders.created_at BETWEEN '$from' AND '$to'" : "") . '
            )
        ) - (
            SELECT SUM(paid_amount)
            FROM orders
            WHERE orders.customer_id = customers.id
            ' . ($from && $to ? "AND orders.created_at BETWEEN '$from' AND '$to'" : "") . '
        )'));
    
        return response()->json([
            'total_sales' => $totalSales,
            'total_revenue' => $totalRevenue,
            'total_debt' => $totalDebt
        ]);
    }
    

    // ✅ Get Top-Selling Products
    public function topSellingProducts()
    {
        $topProducts = OrderProduct::select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->with('product:id,name')
            ->take(5) // ✅ Get top 5 products
            ->get();

        return response()->json($topProducts);
    }

    // ✅ Monthly Sales Report
    public function monthlySalesReport()
    {
        $monthlySales = Order::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('SUM(paid_amount) as revenue'),
            DB::raw('SUM((SELECT SUM(price * quantity) FROM order_products WHERE order_products.order_id = orders.id)) as sales')
        )
        ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
        ->orderBy('month', 'asc')
        ->get();

        return response()->json($monthlySales);
    }
}
