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
/**
     * Store a new order, either via IMS admin or POS checkout.
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Branch on source
        $source = $request->input('source', 'admin');

        if ($source === 'pos') {
            // 1) Validate incoming POS data
            $data = $request->validate([
                'cashier_id'           => 'required|exists:users,id',
                'subtotal_czk'         => 'required|numeric|min:0',
                'tip_czk'              => 'required|numeric|min:0',
                'grand_total_czk'      => 'required|numeric|min:0',
                'rounded_total_czk'    => 'required|numeric|min:0',
                'payment_currency'     => 'required|in:CZK,EUR',
                'amount_tendered_czk'  => 'required_if:payment_currency,CZK|numeric|min:0',
                'amount_tendered_eur'  => 'required_if:payment_currency,EUR|numeric|min:0',
                'change_due_czk'       => 'nullable|numeric',
                'change_due_eur'       => 'nullable|numeric',
                'payment_method'       => 'required|in:cash,card',
                'items'                => 'required|array|min:1',
                'items.*.product_id'   => 'required|exists:products,id',
                'items.*.quantity'     => 'required|integer|min:1',
                'items.*.unit_price'   => 'required|numeric|min:0',
            ]);

            // 2) Server-side rounding check
            $computed = ceil(($data['subtotal_czk'] + $data['tip_czk']) * 2) / 2;
            if ($computed != $data['rounded_total_czk']) {
                return response()->json(['message' => 'Invalid rounding'], 422);
            }

            // 3) Extract only the order columns for creation
            $orderPayload = [
                'cashier_id'          => $data['cashier_id'],
                'subtotal_czk'        => $data['subtotal_czk'],
                'tip_czk'             => $data['tip_czk'],
                'grand_total_czk'     => $data['grand_total_czk'],
                'rounded_total_czk'   => $data['rounded_total_czk'],
                'payment_currency'    => $data['payment_currency'],
                'amount_tendered_czk' => $data['amount_tendered_czk'] ?? null,
                'amount_tendered_eur' => $data['amount_tendered_eur'] ?? null,
                'change_due_czk'      => $data['change_due_czk'] ?? null,
                'change_due_eur'      => $data['change_due_eur'] ?? null,
                'payment_method'      => $data['payment_method'],
                'source'              => 'pos',
                // if you have nullable customer_id, delivery_supplier_id:
                'customer_id'         => $request->input('customer_id'),
                'delivery_supplier_id'=> $request->input('delivery_supplier_id'),
            ];

            // 4) Create the order
            $order = OrderModel::create($orderPayload);

            // 5) Attach items and decrement stock
            foreach ($data['items'] as $item) {
                $order->orderProducts()->create([
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'price'      => $item['unit_price'],
                ]);
                Product::find($item['product_id'])
                    ->decrement('actual_quantity', $item['quantity']);
            }

            return response()->json($order->load('orderProducts.product'), 201);
        }

        // ------------------------
        // Legacy Admin IMS flow
        // ------------------------
        $adminData = $request->validate([
            'customer_id'          => 'required|exists:customers,id',
            'delivery_supplier_id' => 'required|exists:delivery_suppliers,id',
            'paid_amount'          => 'nullable|numeric|min:0',
        ]);

        $order = OrderModel::create([
            'customer_id'           => $adminData['customer_id'],
            'delivery_supplier_id'  => $adminData['delivery_supplier_id'],
            'paid_amount'           => $adminData['paid_amount'] ?? 0,
            'source'                => 'admin',
        ]);

        return response()->json([
            'message' => 'Order created successfully',
            'order'   => $order
        ], 201);
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
