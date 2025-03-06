<?php

namespace App\Http\Controllers;

use App\Models\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StorageController extends Controller
{
    // ✅ Allow Admin & Staff to view storage locations
    public function index()
    {
        return response()->json(Storage::all(), 200);
    }

    // ✅ Only Admins can create new storage locations
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $request->validate([
            'name' => 'required|string',
            'location' => 'required|string',
        ]);

        $storage = Storage::create($request->all());

        return response()->json(['message' => 'Storage created successfully', 'storage' => $storage], 201);
    }

    // ✅ Allow Admin & Staff to view a single storage location
    public function show($id)
    {
        $storage = Storage::find($id);

        if (!$storage) {
            return response()->json(['message' => 'Storage not found'], 404);
        }

        return response()->json($storage, 200);
    }

    // ✅ Only Admins can update storage information
    public function update(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $storage = Storage::find($id);

        if (!$storage) {
            return response()->json(['message' => 'Storage not found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|string',
            'location' => 'sometimes|string',
        ]);

        $storage->update($request->all());

        return response()->json(['message' => 'Storage updated successfully', 'storage' => $storage], 200);
    }

    // ✅ Only Admins can delete storage locations
    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $storage = Storage::find($id);

        if (!$storage) {
            return response()->json(['message' => 'Storage not found'], 404);
        }

        $storage->delete();

        return response()->json(['message' => 'Storage deleted successfully'], 200);
    }
}
