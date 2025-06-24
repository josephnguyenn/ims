<?php

namespace App\Http\Controllers;

use App\Services\InventoryService;
use App\Models\Order as OrderModel; // ✅ Avoid class conflict
use App\Models\OrderProduct;
use App\Models\Customer;
use Illuminate\Support\Facades\DB; // ✅ Thêm dòng này
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
                'tip_czk'              => 'nullable|numeric|min:0',
                'tip_eur'              => 'nullable|numeric|min:0',
                'grand_total_czk'      => 'required|numeric|min:0',
                'rounded_total_czk'    => 'required|numeric|min:0',
                'payment_currency'     => 'required|in:CZK,EUR',
                'amount_tendered_czk'  => 'required_if:payment_currency,CZK|numeric|min:0',
                'amount_tendered_eur'  => 'required_if:payment_currency,EUR|numeric|min:0',
                'change_due_czk'       => 'nullable|numeric',
                'change_due_eur'       => 'nullable|numeric',
                'payment_method'       => 'required|in:cash,card,transfer',
                'items'                => 'required|array|min:1',
                'items.*.code'         => 'required|string|exists:products,code',
                'items.*.quantity'     => 'required|integer|min:1',
                'paid_amount' => 'required|numeric|min:0', // ✅ ADD THIS
                'items.*.unit_price'   => 'required|numeric|min:0',
            ]);

            // 2) Server-side rounding check
            if ($data['payment_currency'] === 'CZK') {
                $computed = ceil(($data['subtotal_czk'] + ($data['tip_czk'] ?? 0)) * 2) / 2;
                if ($computed != $data['rounded_total_czk']) {
                    return response()->json(['message' => 'Invalid rounding'], 422);
                }
            }

            

            // 3) Extract only the order columns for creation
            $orderPayload = [
                'cashier_id'          => $data['cashier_id'],
                'subtotal_czk'        => $data['subtotal_czk'],
                'tip_czk'             => $data['tip_czk'] ?? 0,
                'tip_eur'             => $data['tip_eur'] ?? 0,
                'grand_total_czk'     => $data['grand_total_czk'],
                'rounded_total_czk'   => $data['rounded_total_czk'],
                'payment_currency'    => $data['payment_currency'],
                'amount_tendered_czk' => $data['amount_tendered_czk'] ?? null,
                'amount_tendered_eur' => $data['amount_tendered_eur'] ?? null,
                'change_due_czk'      => $data['change_due_czk'] ?? null,
                'change_due_eur'      => $data['change_due_eur'] ?? null,
                'payment_method'      => $data['payment_method'],
                'source'              => 'pos',
                'customer_id'         => $request->input('customer_id'),
                'delivery_supplier_id'=> $request->input('delivery_supplier_id'),
                'paid_amount'         => $data['paid_amount'], // ✅ Add this line
            ];

            // 4) Create the order
            $order = OrderModel::create($orderPayload);

            $shiftId = $this->determineCurrentShiftId();
            if ($shiftId) {
                $order->update(['shift_id' => $shiftId]);
            }

            // 5) Attach items and decrement stock
            foreach ($data['items'] as $item) {
            $code = $item['code'];
            $qty  = $item['quantity'];

            // Get all matching products (shipments) for this code
            $products = Product::where('code', $code)
                ->where('actual_quantity', '>', 0)
                ->orderByRaw('ISNULL(expired_date), expired_date ASC') // NULL last
                ->orderBy('created_at', 'asc') // fallback FIFO
                ->get();

            $remaining = $qty;
            $usedProductId = null;

            foreach ($products as $product) {
                if ($remaining <= 0) break;

                $deductQty = min($product->actual_quantity, $remaining);
                $product->decrement('actual_quantity', $deductQty);
                $remaining -= $deductQty;

                // Save the first product ID used (to log in order_products)
                if (!$usedProductId) {
                    $usedProductId = $product->id;
                }
            }

            if ($remaining > 0) {
                return response()->json([
                    'message' => "Không đủ hàng cho mã $code",
                ], 422);
            }

            // Save the order product entry using the first product ID used
            $order->orderProducts()->create([
                'product_id' => $usedProductId,
                'quantity'   => $qty,
                'price'      => $item['unit_price'],
                'tax'        => $product->tax,  // ✅ Correct key name

            ]);
        }

            return response()->json([
                'message' => 'Order created successfully',
                'order'   => $order
            ], 201);

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

    private function determineCurrentShiftId(): ?int {
        $now = now()->format('H:i:s');
        \Log::info('Current time for shift check: ' . $now);

        $shift = DB::table('shifts')
            ->where(function ($q) use ($now) {
                $q->whereRaw('? BETWEEN start_time AND end_time', [$now])
                ->whereRaw('start_time < end_time');
            })
            ->orWhere(function ($q) use ($now) {
                $q->whereRaw('start_time > end_time')
                ->whereRaw('? >= start_time OR ? < end_time', [$now, $now]);
            })
            ->orderBy('sort_order')
            ->value('id');

        \Log::info('Determined shift_id: ' . $shift);
        return $shift;
    }



}
