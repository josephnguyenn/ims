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
            'tax' => 'nullable|numeric|min:0'
        ]);

        $product = Product::create($request->all());

        // ✅ Update shipment cost
        $shipment = Shipment::find($request->shipment_id);
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
            'category' => 'sometimes|string',
            'shipment_id' => 'sometimes|exists:shipments,id',
            'tax' => 'sometimes|numeric|min:0'
        ]);

        $product->update($request->all());

        // ✅ Prevent expired_date, total_cost, actual_quantity from manual updates
        $product->total_cost = $product->original_quantity * $product->cost;
        $product->actual_quantity = $product->original_quantity;
        $product->expired_date = $product->shipment->expired_date;
        $product->save();

        // ✅ Update shipment cost
        $shipment = Shipment::find($product->shipment_id);
        $shipment->calculateTotalCost();

        return response()->json(['message' => 'Product updated successfully', 'product' => $product], 200);
    }

    // ✅ Only Admins can delete a product
    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $shipment = Shipment::find($product->shipment_id);

        $product->delete();

        // ✅ Update shipment cost after deleting a product
        if ($shipment) {
            $shipment->calculateTotalCost();
        }

        return response()->json(['message' => 'Product deleted successfully'], 200);
    }

}
