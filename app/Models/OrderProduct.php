<?php

namespace App\Http\Controllers;

use App\Models\OrderProduct;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderProductController extends Controller
{
    // ✅ View all order products (Admin & Staff)
    public function index()
    {
        return response()->json(OrderProduct::with('order', 'product')->get(), 200);
    }

    // ✅ Only Admins can add products to an order
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::find($request->product_id);

        $orderProduct = OrderProduct::create([
            'order_id' => $request->order_id,
            'product_id' => $request->product_id,
            'quantity' => $request->quantity,
            'price' => $product->price
        ]);

        // ✅ Update total price of order
        $order = Order::find($request->order_id);
        $order->updateTotalPrice();

        return response()->json(['message' => 'Product added to order successfully', 'order_product' => $orderProduct], 201);
    }

    // ✅ View products in a single order (Admin & Staff)
    public function show($order_id)
    {
        $orderProducts = OrderProduct::where('order_id', $order_id)->with('product')->get();

        if ($orderProducts->isEmpty()) {
            return response()->json(['message' => 'No products found for this order'], 404);
        }

        return response()->json($orderProducts, 200);
    }

    // ✅ Only Admins can remove a product from an order
    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $orderProduct = OrderProduct::find($id);

        if (!$orderProduct) {
            return response()->json(['message' => 'Order product not found'], 404);
        }

        $order = $orderProduct->order;
        $orderProduct->delete();

        // ✅ Update total price of order
        $order->updateTotalPrice();

        return response()->json(['message' => 'Product removed from order successfully'], 200);
    }
}
