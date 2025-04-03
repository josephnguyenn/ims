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
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
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
            'expired_date' => 'nullable|date', // ✅ Just validate format here
        ]);

        $shipment = Shipment::find($request->shipment_id);

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
        $product->expired_date = $request->expired_date ?? $shipment->expired_date;
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
            if (Auth::user()->role !== 'admin') {
                return response()->json(['message' => 'Access denied'], 403);
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
                'category' => 'sometimes|string',
                'shipment_id' => 'sometimes|exists:shipments,id',
                'tax' => 'sometimes|numeric|min:0'
            ]);

            // Apply changes
            $product->update($request->only([
                'name', 'code', 'original_quantity', 'price', 'cost', 'category', 'shipment_id', 'tax', 'expired_date'
            ]));

            // Recalculate total and quantity
            $product->total_cost = $product->original_quantity * $product->cost;
            $product->actual_quantity = $product->original_quantity;

            // If expired_date is still empty, fallback to shipment’s
            if (!$product->expired_date && $product->shipment) {
                $product->expired_date = $product->shipment->expired_date;
            }
            // overwrite expired_date
            if ($request->has('expired_date')) {
                $product->expired_date = $request->expired_date;
            }        

            $product->save();

            // Update shipment cost
            $shipment = Shipment::find($product->shipment_id);
            $shipment?->calculateTotalCost();

            \Log::info('Update request data:', $request->all());
            \Log::info('Product updated:', $product->toArray());

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
        
}