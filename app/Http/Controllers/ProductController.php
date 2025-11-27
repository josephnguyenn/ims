<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    // ✅ View all products with pagination
    public function index(Request $request)
    {
        // Start with the base query, eager‐loading shipment & category
        $query = Product::with(['shipment', 'category']);

        // If a category_id is provided, filter by it
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        }

        // Add search functionality with optimized indexes
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('code', 'LIKE', "%{$search}%");
            });
        }

        // Add low stock filter
        if ($request->query('low_stock')) {
            $query->where('actual_quantity', '<', 10);
        }

        // Add price range filter
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->query('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->query('max_price'));
        }

        // Default sorting by name for better performance with index
        $query->orderBy('name', 'asc');

        // Pagination - default 50 items per page, max 100
        $perPage = min($request->query('per_page', 50), 100);

        if ($request->query('paginate', true)) {
            $products = $query->paginate($perPage);
        } else {
            // For cases where all products are needed (like dropdowns)
            $products = $query->get();
        }

        return response()->json($products, 200);
    }

    public function searchByBarcode(Request $request)
    {
        $barcode = $request->query('barcode');
        if (! $barcode) {
            return response()->json(null, 200);
        }

        $product = Product::where('code', $barcode)
            ->with('category')
            ->first();

        return response()->json($product, 200);
    }

    // ✅ Only Admins can create products
    public function store(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'name' => 'required|string',
            'code' => 'required|string',
            'original_quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'category_id' => 'required|exists:product_categories,id',
            'shipment_id' => 'required|exists:shipments,id',
            'tax' => 'nullable|numeric|min:0',
            'expired_date' => 'nullable|date',
            'expiry_mode' => 'required|in:custom,inherit,none',
        ]);

        $shipment = Shipment::find($request->shipment_id);

        // ✅ Determine expired_date based on expiry_mode
        switch ($request->expiry_mode) {
            case 'custom':
                $expiredDate = $request->expired_date;
                break;
            case 'inherit':
                $expiredDate = $shipment->expired_date;
                break;
            case 'none':
            default:
                $expiredDate = null;
                break;
        }

        $product = new Product;
        $product->name = $request->name;
        $product->code = $request->code;
        $product->original_quantity = $request->original_quantity;
        $product->actual_quantity = $request->original_quantity;
        $product->price = $request->price;
        $product->cost = $request->cost;
        $product->total_cost = $request->original_quantity * $request->cost;
        $product->category_id = $request->category_id;
        $product->shipment_id = $request->shipment_id;
        $product->tax = $request->tax;
        $product->expired_date = $expiredDate;
        $product->save();

        $shipment->calculateTotalCost();

        return response()->json(['message' => 'Product created successfully', 'product' => $product], 201);
    }

    // ✅ View a single product
    public function show($id)
    {
        $product = Product::with('shipment')->find($id);

        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json($product, 200);
    }

    // ✅ Only Admins can update products
    public function update(Request $request, $id)
    {
        \Log::info('Update request received:', $request->all());

        if (! Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $product = Product::find($id);

        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|string',
            'code' => 'sometimes|string',
            'original_quantity' => 'sometimes|integer|min:0',
            'price' => 'sometimes|numeric|min:0',
            'cost' => 'sometimes|numeric|min:0',
            'expired_date' => 'nullable|date',
            'expiry_mode' => 'required|in:custom,inherit,none',
            'category_id' => 'required|exists:product_categories,id',
            'shipment_id' => 'sometimes|exists:shipments,id',
            'tax' => 'sometimes|numeric|min:0',
        ]);

        $shipment = Shipment::find($request->shipment_id ?? $product->shipment_id);

        // ✅ Fill all other fields *except* expired_date
        $product->fill($request->only([
            'name', 'code', 'original_quantity', 'price', 'cost', 'category_id', 'shipment_id', 'tax',
        ]));

        // ✅ Now handle expired_date *last* based on mode
        switch ($request->expiry_mode) {
            case 'custom':
                $product->expired_date = $request->expired_date;
                break;
            case 'inherit':
                $product->expired_date = $shipment?->expired_date;
                break;
            case 'none':
            default:
                $product->expired_date = null;
                break;
        }

        // ✅ Recalculate values
        $product->total_cost = $product->original_quantity * $product->cost;
        $product->actual_quantity = $product->original_quantity;

        $product->save();
        $shipment?->calculateTotalCost();

        return response()->json(['message' => 'Product updated successfully', 'product' => $product], 200);
    }

    public function searchByCode(Request $request)
    {
        $code = $request->query('code');
        if (! $code) {
            return response()->json([], 200);
        }

        // 1) Lấy tất cả product cùng code và còn actual_quantity > 0
        $items = Product::with('shipment')
            ->where('code', $code)
            ->where('actual_quantity', '>', 0)
            ->get();

        // 2) Sort theo ngày lô (order_date) ASC — lô cũ nhất trước
        $sorted = $items->sortBy(function ($p) {
            // nếu shipment có order_date, dùng nó; nếu không, fallback created_at của product
            return optional($p->shipment)->order_date
                ?? $p->created_at;
        })->values();

        // 3) Map sang cấu trúc JS cần dùng
        $result = $sorted->map(function ($p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'price' => $p->price,
                'code' => $p->code,
                'tax' => $p->tax, // ✅ Add this line
                'shipment_id' => $p->shipment_id,
                'actual_quantity' => $p->actual_quantity,
                'created_at' => optional($p->shipment)->order_date ?? $p->created_at->toDateTimeString(),
            ];
        });

        return response()->json($result, 200);
    }

    public function destroy($id)
    {
        $user = Auth::user();

        // ✅ Check user role
        if (! in_array($user->role, ['admin', 'manager'])) {
            return response()->json([
                'message' => 'Unauthorized. Only admin or manager can delete products.',
            ], 403);
        }

        $product = Product::find($id);

        if (! $product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        try {
            $product->delete();

            return response()->json(['message' => 'Product deleted successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete product',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Bulk update products
     */
    public function bulkUpdate(Request $request)
    {
        $user = Auth::user();

        if (! in_array($user->role, ['admin', 'manager'])) {
            return response()->json([
                'message' => 'Unauthorized. Only admin or manager can bulk update products.',
            ], 403);
        }

        $validated = $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'updates' => 'required|array',
        ]);

        try {
            $updatedCount = 0;
            
            foreach ($validated['product_ids'] as $id) {
                $product = Product::find($id);
                if ($product) {
                    // Only update allowed fields
                    $allowedFields = ['price', 'cost', 'tax', 'category_id', 'selling_price'];
                    foreach ($validated['updates'] as $field => $value) {
                        if (in_array($field, $allowedFields)) {
                            $product->{$field} = $value;
                        }
                    }
                    
                    // Recalculate if cost changed
                    if (isset($validated['updates']['cost'])) {
                        $product->total_cost = $product->original_quantity * $product->cost;
                    }
                    
                    $product->save();
                    $updatedCount++;
                }
            }

            return response()->json([
                'message' => "Successfully updated {$updatedCount} products",
                'updated_count' => $updatedCount
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Bulk update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Bulk delete products
     */
    public function bulkDelete(Request $request)
    {
        $user = Auth::user();

        if (! in_array($user->role, ['admin', 'manager'])) {
            return response()->json([
                'message' => 'Unauthorized. Only admin or manager can bulk delete products.',
            ], 403);
        }

        $validated = $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
        ]);

        try {
            $deletedCount = Product::whereIn('id', $validated['product_ids'])->delete();

            return response()->json([
                'message' => "Successfully deleted {$deletedCount} products",
                'deleted_count' => $deletedCount
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Bulk delete failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

