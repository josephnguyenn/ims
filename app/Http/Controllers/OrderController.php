<?php

namespace App\Http\Controllers;

use App\Models\Order as OrderModel; // ✅ Avoid class conflict
use App\Models\OrderProduct;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    // ✅ View all orders (Admin & Staff)
    public function index(Request $request)
    {
        $query = OrderModel::with('customer', 'deliverySupplier', 'orderProducts.product');
        
        // Check if both from and to dates are provided
        if ($request->has('from') && $request->has('to')) {
            $from = $request->input('from') . " 00:00:00";
            $to = $request->input('to') . " 23:59:59";
            $query->whereBetween('created_at', [$from, $to]);
        }
        
        $orders = $query->get(); // This line ensures the filtered query is executed.
        return response()->json($orders, 200);
    }
    

    // ✅ Only Admins can create an order
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }


        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'delivery_supplier_id' => 'required|exists:delivery_suppliers,id'
        ]);

        $order = OrderModel::create([
            'customer_id' => $request->customer_id,
            'delivery_supplier_id' => $request->delivery_supplier_id,
            'paid_amount' => 0,
            'total_price' => 0 // ✅ Initial total price is 0
        ]);

        return response()->json(['message' => 'Order created successfully', 'order' => $order], 201);
    }

    // ✅ View a single order (Admin & Staff)
    public function show($id)
    {
        $order = OrderModel::with('customer', 'deliverySupplier', 'orderProducts.product')->find($id);

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        return response()->json($order, 200);
    }

    // ✅ Update an order (Only Admin)
    public function update(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }


        $order = OrderModel::find($id);
        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $request->validate([
            'paid_amount' => 'sometimes|numeric|min:0',
            'delivery_supplier_id' => 'sometimes|exists:delivery_suppliers,id'
        ]);

        $order->update($request->only(['paid_amount', 'delivery_supplier_id']));
        return response()->json(['message' => 'Order updated successfully', 'order' => $order], 200);
    }

    // ✅ Delete an order (Only Admin)
    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'manager') {
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
