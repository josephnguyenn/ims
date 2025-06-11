<?php

namespace App\Http\Controllers;

use App\Models\OrderProduct;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderProductController extends Controller
{
    // ✅ View all order products 
    public function index()
    {
        return response()->json(OrderProduct::with('order', 'product')->get(), 200);
    }

    // ✅ add products to an order
    public function store(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'order_id'   => 'required|exists:orders,id',
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        // 1) Load order & desired qty
        $order = Order::findOrFail($request->order_id);
        $qty   = $request->quantity;

        // 2) Grab one product row so we know the code & price
        $initial = Product::findOrFail($request->product_id);
        $code    = $initial->code;

        // 3) Fetch ALL lots (product rows) with that code, in FIFO order
        $lots = Product::with('shipment')
            ->where('code', $code)
            ->where('actual_quantity', '>', 0)
            ->get()
            ->sortBy(function($lot) {
                return $lot->shipment && $lot->shipment->order_date
                    ? $lot->shipment->order_date
                    : $lot->created_at;
            })
            ->values();

        // 4) Check total stock
        $totalStock = $lots->sum('actual_quantity');
        if ($totalStock < $qty) {
            return response()->json(['message' => 'Insufficient stock'], 400);
        }

        // 5) Deduct across shipments, then create the order line
        DB::transaction(function() use ($order, $lots, $qty, $initial) {
            $remaining = $qty;

            foreach ($lots as $lot) {
                if ($remaining <= 0) break;
                $take = min($lot->actual_quantity, $remaining);
                $lot->actual_quantity -= $take;
                $lot->save();
                $remaining -= $take;
            }

            OrderProduct::create([
                'order_id'   => $order->id,
                'product_id' => $initial->id,
                'quantity'   => $qty,
                'price'      => $initial->price,
            ]);

            $order->updateTotalPrice();
        });

        return response()->json([
            'message'        => 'Product added to order successfully',
            'remainingStock' => $initial->fresh()->actual_quantity,
        ], 201);
    }


    // ✅ View products in a single order (Admin & Staff)
    public function show($order_id)
    {
        $orderProducts = OrderProduct::where('order_id', $order_id)->with('product')->get();

        return response()->json($orderProducts, 200);
    }

    // ✅ Update order product quantity 
    public function update(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $orderProduct = OrderProduct::find($id);

        if (!$orderProduct) {
            return response()->json(['message' => 'Order product not found'], 404);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::find($orderProduct->product_id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        // ✅ Adjust stock based on quantity update
        $oldQuantity = $orderProduct->quantity;
        $newQuantity = $request->quantity;

        if ($newQuantity > $oldQuantity) {
            $difference = $newQuantity - $oldQuantity;
            if ($product->actual_quantity < $difference) {
                return response()->json(['message' => 'Insufficient stock'], 400);
            }
            $product->actual_quantity -= $difference;
        } else {
            $difference = $oldQuantity - $newQuantity;
            $product->actual_quantity += $difference;
        }

        $product->save();

        // ✅ Update order product quantity
        $orderProduct->update(['quantity' => $newQuantity]);

        // ✅ Update total price of order
        $orderProduct->order->updateTotalPrice();

        return response()->json(['message' => 'Order product updated successfully', 'order_product' => $orderProduct], 200);
    }

    // ✅ Only Admins can remove a product from an order
    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'manager') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $orderProduct = OrderProduct::find($id);

        if (!$orderProduct) {
            return response()->json(['message' => 'Order product not found'], 404);
        }

        $order = $orderProduct->order;

        // ✅ Restore stock when removing an order product
        $product = $orderProduct->product;
        $product->actual_quantity += $orderProduct->quantity;
        $product->save();

        $orderProduct->delete();

        // ✅ Auto-update order total price without modifying `total_price` in DB
        $order->updateTotalPrice();

        return response()->json(['message' => 'Product removed from order successfully'], 200);
    }
}
