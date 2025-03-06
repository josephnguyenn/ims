<?php

namespace App\Http\Controllers;

use App\Models\Order as OrderModel; // ✅ Fix name conflict
use App\Models\OrderProduct;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    // ✅ View all orders (Admin & Staff)
    public function index()
    {
        return response()->json(OrderModel::with('customer', 'orderProducts.product')->get(), 200);
    }

    // ✅ Only Admins can create an order
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
        ]);

        $order = OrderModel::create([
            'customer_id' => $request->customer_id,
            'paid_amount' => 0,
        ]);

        return response()->json(['message' => 'Order created successfully', 'order' => $order], 201);
    }

    // ✅ View a single order (Admin & Staff)
    public function show($id)
    {
        $order = OrderModel::with('customer', 'orderProducts.product')->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json($order, 200);
    }

    // ✅ Update an order (Only Admin)
    public function update(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $order = OrderModel::find($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $request->validate([
            'paid_amount' => 'sometimes|numeric|min:0'
        ]);

        $order->update($request->only(['paid_amount']));
        return response()->json(['message' => 'Order updated successfully', 'order' => $order], 200);
    }

    // ✅ Delete an order (Only Admin)
    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $order = OrderModel::find($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $order->delete();

        return response()->json(['message' => 'Order deleted successfully'], 200);
    }
}
