<?php

namespace App\Http\Controllers;

use App\Models\DeliverySupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeliverySupplierController extends Controller
{
    // ✅ Allow Admin & Staff to view all delivery suppliers
    public function index()
    {
        return response()->json(DeliverySupplier::all(), 200);
    }

    // ✅ Only Admins can create a delivery supplier
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $request->validate([
            'name' => 'required|string|unique:delivery_suppliers'
        ]);

        $supplier = DeliverySupplier::create($request->all());

        return response()->json(['message' => 'Delivery Supplier created successfully', 'supplier' => $supplier], 201);
    }

    // ✅ Allow Admin & Staff to view a single supplier
    public function show($id)
    {
        $supplier = DeliverySupplier::find($id);

        if (!$supplier) {
            return response()->json(['message' => 'Delivery Supplier not found'], 404);
        }

        return response()->json($supplier, 200);
    }

    // ✅ Only Admins can update supplier details
    public function update(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $supplier = DeliverySupplier::find($id);

        if (!$supplier) {
            return response()->json(['message' => 'Delivery Supplier not found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|unique:delivery_suppliers,name,' . $supplier->id
        ]);

        $supplier->update($request->all());

        return response()->json(['message' => 'Delivery Supplier updated successfully', 'supplier' => $supplier], 200);
    }

    // ✅ Only Admins can delete a delivery supplier
    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $supplier = DeliverySupplier::find($id);

        if (!$supplier) {
            return response()->json(['message' => 'Delivery Supplier not found'], 404);
        }

        $supplier->delete();

        return response()->json(['message' => 'Delivery Supplier deleted successfully'], 200);
    }
}
