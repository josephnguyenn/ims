<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    protected ?GeminiService $gemini = null;

    public function __construct()
    {
        try {
            $this->gemini = new GeminiService();
        } catch (\Exception $e) {
            \Log::error('Failed to initialize GeminiService: ' . $e->getMessage());
        }
    }

    /**
     * Get sales trends with date range
     */
    public function salesTrends(Request $request)
    {
        try {
            $days = $request->get('days', 30);
            $startDate = Carbon::now()->subDays($days);

            $data = DB::table('orders')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as total_orders'),
                    DB::raw('SUM(total) as total_revenue')
                )
                ->where('created_at', '>=', $startDate)
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy(DB::raw('DATE(created_at)'))
                ->get();

            return response()->json([
                'data' => $data,
                'period' => "{$days} days"
            ]);
        } catch (\Exception $e) {
            \Log::error('Sales Trends Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch sales trends',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ], 500);
        }
    }

    /**
     * Get top performing products
     */
    public function topProducts(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);

            $products = DB::table('order_products')
                ->join('products', 'order_products.product_id', '=', 'products.id')
                ->select(
                    'products.name',
                    'products.code',
                    DB::raw('SUM(order_products.quantity) as total_sold'),
                    DB::raw('SUM(order_products.subtotal) as total_revenue')
                )
                ->groupBy('products.id', 'products.name', 'products.code')
                ->orderByDesc('total_sold')
                ->limit($limit)
                ->get();

            return response()->json(['data' => $products]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch top products',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get revenue analysis
     */
    public function revenueAnalysis(Request $request)
    {
        try {
            $period = $request->get('period', 'month'); // day, week, month, year

            $data = [
                'total_revenue' => DB::table('orders')->sum('total'),
                'total_orders' => DB::table('orders')->count(),
                'average_order_value' => DB::table('orders')->avg('total'),
                'by_period' => []
            ];

            // Monthly breakdown
            if ($period === 'month') {
                $data['by_period'] = DB::table('orders')
                    ->select(
                        DB::raw('DATE_FORMAT(created_at, "%Y-%m") as period'),
                        DB::raw('COUNT(*) as orders'),
                        DB::raw('SUM(total) as revenue')
                    )
                    ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
                    ->orderBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'), 'desc')
                    ->limit(12)
                    ->get();
            }

            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error('Revenue Analysis Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch revenue analysis',
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Get inventory turnover
     */
    public function inventoryTurnover()
    {
        try {
            $products = DB::table('products')
                ->leftJoin('order_products', 'products.id', '=', 'order_products.product_id')
                ->select(
                    'products.id',
                    'products.name',
                    'products.actual_quantity',
                    DB::raw('COALESCE(SUM(order_products.quantity), 0) as total_sold'),
                    DB::raw('CASE 
                        WHEN products.actual_quantity > 0 
                        THEN ROUND(COALESCE(SUM(order_products.quantity), 0) / products.actual_quantity, 2)
                        ELSE 0 
                    END as turnover_rate')
                )
                ->groupBy('products.id', 'products.name', 'products.actual_quantity')
                ->havingRaw('total_sold > 0')
                ->orderByDesc('turnover_rate')
                ->limit(20)
                ->get();

            return response()->json(['data' => $products]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch inventory turnover',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get AI-powered insights
     */
    public function aiInsights(Request $request)
    {
        try {
            if (!$this->gemini) {
                return response()->json([
                    'type' => $request->get('type', 'sales'),
                    'insights' => 'AI service is not available. Please configure GEMINI_API_KEY.',
                    'generated_at' => now()->toDateTimeString()
                ]);
            }

            $type = $request->get('type', 'sales'); // sales, inventory, recommendations

            switch ($type) {
                case 'sales':
                    // Get recent sales data
                    $salesData = DB::table('orders')
                        ->select(
                            DB::raw('DATE(created_at) as date'),
                            DB::raw('COUNT(*) as orders'),
                            DB::raw('SUM(total) as revenue')
                        )
                        ->where('created_at', '>=', Carbon::now()->subDays(30))
                        ->groupBy(DB::raw('DATE(created_at)'))
                        ->get()
                        ->toArray();

                    $insights = $this->gemini->analyzeSalesData($salesData);
                    break;

                case 'inventory':
                    // Get low stock items
                    $inventoryData = DB::table('products')
                        ->select('name', 'actual_quantity', 'price')
                        ->where('actual_quantity', '<', 10)
                        ->get()
                        ->toArray();

                    $insights = $this->gemini->suggestReorderPoints($inventoryData);
                    break;

                case 'recommendations':
                    // Get product performance
                    $productPerformance = DB::table('order_products')
                        ->join('products', 'order_products.product_id', '=', 'products.id')
                        ->select(
                            'products.name',
                            DB::raw('SUM(order_products.quantity) as total_sold'),
                            DB::raw('SUM(order_products.subtotal) as revenue'),
                            'products.actual_quantity'
                        )
                        ->groupBy('products.id', 'products.name', 'products.actual_quantity')
                        ->orderByDesc('total_sold')
                        ->limit(20)
                        ->get()
                        ->toArray();

                    $insights = $this->gemini->generateProductRecommendations($productPerformance);
                    break;

                default:
                    $insights = 'Invalid insight type';
            }

            return response()->json([
                'type' => $type,
                'insights' => $insights,
                'generated_at' => now()->toDateTimeString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate AI insights',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sales forecast using AI
     */
    public function salesForecast()
    {
        // Get last 90 days of sales
        $historicalData = DB::table('orders')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total) as revenue')
            )
            ->where('created_at', '>=', Carbon::now()->subDays(90))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();

        $forecast = $this->gemini->predictSalesTrend($historicalData);

        return response()->json([
            'forecast' => $forecast,
            'based_on_days' => 90,
            'generated_at' => now()->toDateTimeString()
        ]);
    }
}
