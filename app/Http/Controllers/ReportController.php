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

    public function posReport(Request $request)
    {
        $from     = $request->query('from', now()->subDays(60)->toDateString());
        $to       = $request->query('to', now()->toDateString());
        $shift_id = $request->query('shift_id');

        $query = Order::with(['shift:id,name', 'cashier:id,name'])
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);

        if ($shift_id) {
            $query->where('shift_id', $shift_id);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $summary = [
            'sum_czk_cash'     => 0,
            'sum_czk_card'     => 0,
            'sum_czk_transfer' => 0,
            'sum_eur_cash'     => 0,
            'sum_eur_card'     => 0,
            'sum_eur_transfer' => 0,
            'sum_tip_czk'      => 0,
            'sum_tip_eur'      => 0,
        ];

        $invoices = [];

        foreach ($orders as $o) {
            $inv = [
                'id'                  => $o->id,
                'created_at'          => $o->created_at->format('Y-m-d H:i:s'),
                'cashier_name'        => $o->cashier->name ?? null,
                'cashier_id'          => $o->cashier_id,
                'shift_name'          => $o->shift->name ?? null,
                'payment_method'      => $o->payment_method,
                'payment_currency'    => $o->payment_currency,
                'rounded_total_czk'   => (float) $o->rounded_total_czk,
                'tip_czk'             => (float) $o->tip_czk,
                'tip_eur'             => (float) $o->tip_eur,
                'amount_tendered_czk' => (float) ($o->amount_tendered_czk ?? 0),
                'amount_tendered_eur' => (float) ($o->amount_tendered_eur ?? 0),
            ];

            $invoices[] = $inv;

            // Summary
            $method = $o->payment_method;
            $cur    = $o->payment_currency;

            if ($method === 'cash') {
                if ($cur === 'CZK') $summary['sum_czk_cash'] += $o->amount_tendered_czk;
                if ($cur === 'EUR') $summary['sum_eur_cash'] += $o->amount_tendered_eur;
            } elseif ($method === 'card') {
                if ($cur === 'CZK') $summary['sum_czk_card'] += $o->amount_tendered_czk;
                if ($cur === 'EUR') $summary['sum_eur_card'] += $o->amount_tendered_eur;
            } elseif ($method === 'transfer') {
                if ($cur === 'CZK') $summary['sum_czk_transfer'] += $o->amount_tendered_czk;
                if ($cur === 'EUR') $summary['sum_eur_transfer'] += $o->amount_tendered_eur;
            }

            $summary['sum_tip_czk'] += $o->tip_czk;
            $summary['sum_tip_eur'] += $o->tip_eur;
        }

        return response()->json([
            'invoices' => $invoices,
            'summary'  => $summary,
        ]);
    }

}
