<?php

namespace App\Http\Controllers;

use App\Models\ShipmentSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShipmentSupplierController extends Controller
{
    // ✅ Allow Admin & Staff to view all shipment suppliers
    public function index()
    {
        return response()->json(ShipmentSupplier::all(), 200);
    }

    // ✅ Only Admins can create a shipment supplier
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'manager') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $request->validate([
            'name' => 'required|string|unique:shipment_suppliers'
        ]);

        $supplier = ShipmentSupplier::create($request->all());

        return response()->json(['message' => 'Shipment Supplier created successfully', 'supplier' => $supplier], 201);
    }

    // ✅ Allow Admin & Staff to view a single supplier
    public function show($id)
    {
        $supplier = ShipmentSupplier::find($id);

        if (!$supplier) {
            return response()->json(['message' => 'Shipment Supplier not found'], 404);
        }

        return response()->json($supplier, 200);
    }

    // ✅ Only Admins can update supplier details
    public function update(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'manager') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $supplier = ShipmentSupplier::find($id);

        if (!$supplier) {
            return response()->json(['message' => 'Shipment Supplier not found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|unique:shipment_suppliers,name,' . $supplier->id
        ]);

        $supplier->update($request->all());

        return response()->json(['message' => 'Shipment Supplier updated successfully', 'supplier' => $supplier], 200);
    }

    // ✅ Only Admins can delete a shipment supplier
    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $supplier = ShipmentSupplier::find($id);

        if (!$supplier) {
            return response()->json(['message' => 'Shipment Supplier not found'], 404);
        }

        $supplier->delete();

        return response()->json(['message' => 'Shipment Supplier deleted successfully'], 200);
    }
}
