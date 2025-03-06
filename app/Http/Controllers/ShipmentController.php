<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentSupplier;
use App\Models\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShipmentController extends Controller
{
    // ✅ View all shipments (cost is now dynamically calculated)
    public function index()
    {
        $shipments = Shipment::with(['supplier', 'storage'])->get();

        // Append cost dynamically
        $shipments->each(function ($shipment) {
            $shipment->cost = $shipment->cost; 
        });

        return response()->json($shipments, 200);
    }

    // ✅ Create shipment (cost removed from request)
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

    // ✅ View a single shipment (cost is calculated dynamically)
    public function show($id)
    {
        $shipment = Shipment::with(['supplier', 'storage'])->find($id);

        if (!$shipment) {
            return response()->json(['message' => 'Shipment not found'], 404);
        }

        $shipment->cost = $shipment->cost; // Add cost dynamically

        return response()->json($shipment, 200);
    }
}
