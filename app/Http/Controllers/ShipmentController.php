<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShipmentController extends Controller
{
    // ✅ View all shipments
    public function index()
    {
        return response()->json(Shipment::with(['supplier', 'storage'])->get(), 200);
    }

    // ✅ Create a new shipment
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $request->validate([
            'shipment_supplier_id' => 'required|exists:shipment_suppliers,id',
            'storage_id' => 'required|exists:storages,id',
            'order_date' => 'required|date',
            'received_date' => 'nullable|date',
            'expired_date' => 'nullable|date'
        ]);

        $shipment = Shipment::create($request->all());

        return response()->json(['message' => 'Shipment created successfully', 'shipment' => $shipment], 201);
    }

    // ✅ View a single shipment
    public function show($id)
    {
        $shipment = Shipment::with(['supplier', 'storage'])->find($id);

        if (!$shipment) {
            return response()->json(['message' => 'Shipment not found'], 404);
        }

        return response()->json($shipment, 200);
    }

    // ✅ UPDATE SHIPMENT (Fixing BadMethodCallException)
    public function update(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $shipment = Shipment::find($id);

        if (!$shipment) {
            return response()->json(['message' => 'Shipment not found'], 404);
        }

        $request->validate([
            'shipment_supplier_id' => 'sometimes|exists:shipment_suppliers,id',
            'storage_id' => 'sometimes|exists:storages,id',
            'order_date' => 'sometimes|date',
            'received_date' => 'nullable|date',
            'expired_date' => 'nullable|date'
        ]);

        $shipment->update($request->all());

        return response()->json(['message' => 'Shipment updated successfully', 'shipment' => $shipment], 200);
    }

    // ✅ DELETE SHIPMENT (Fixing BadMethodCallException)
    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $shipment = Shipment::find($id);

        if (!$shipment) {
            return response()->json(['message' => 'Shipment not found'], 404);
        }

        $shipment->delete();

        return response()->json(['message' => 'Shipment deleted successfully'], 200);
    }
}
