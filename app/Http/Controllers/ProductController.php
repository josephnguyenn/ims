<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    // ✅ View all products
    public function index()
    {
        return response()->json(Product::with('shipment')->get(), 200);
    }

    // ✅ Only Admins can create products
    public function store(Request $request)
{
    if (!Auth::check()) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $request->validate([
        'name' => 'required|string',
        'code' => 'required|string',
        'original_quantity' => 'required|integer|min:0',
        'price' => 'required|numeric|min:0',
        'cost' => 'required|numeric|min:0',
        'category' => 'required|string',
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

    $product = new Product();
    $product->name = $request->name;
    $product->code = $request->code;
    $product->original_quantity = $request->original_quantity;
    $product->actual_quantity = $request->original_quantity;
    $product->price = $request->price;
    $product->cost = $request->cost;
    $product->total_cost = $request->original_quantity * $request->cost;
    $product->category = $request->category;
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

            if (!$product) {
                return response()->json(['message' => 'Product not found'], 404);
            }

            return response()->json($product, 200);
        }

        // ✅ Only Admins can update products
    public function update(Request $request, $id)
        {
            \Log::info('Update request received:', $request->all());

            if (!Auth::check()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
        
            $product = Product::find($id);
        
            if (!$product) {
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
                'category' => 'sometimes|string',
                'shipment_id' => 'sometimes|exists:shipments,id',
                'tax' => 'sometimes|numeric|min:0',
            ]);
        
            $shipment = Shipment::find($request->shipment_id ?? $product->shipment_id);
        
            // ✅ Fill all other fields *except* expired_date
            $product->fill($request->only([
                'name', 'code', 'original_quantity', 'price', 'cost', 'category', 'shipment_id', 'tax'
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
        
            if (!$code) {
                return response()->json([], 200); // Return empty list if no code
            }
        
            $products = Product::where('code', 'like', "%$code%")
                ->select('id', 'name', 'code', 'price', 'cost', 'tax', 'category')
                ->limit(10)
                ->get();
        
            return response()->json($products, 200);
        }

    public function destroy($id)
    {
        $user = Auth::user();

        // ✅ Check user role
        if (!in_array($user->role, ['admin', 'manager'])) {
            return response()->json([
                'message' => 'Unauthorized. Only admin or manager can delete products.'
            ], 403);
        }

        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        try {
            $product->delete();
            return response()->json(['message' => 'Product deleted successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete product',
                'error' => $e->getMessage()
            ], 500);
        }
    }
        
}


