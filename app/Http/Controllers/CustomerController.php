<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    // ✅ Allow Admin & Staff to view all customers
    public function index()
    {
        $customers = Customer::withCount('orders')->get();

        $customers->each(function ($customer) {
            $customer->total_orders = $customer->total_orders; // ✅ Include total_orders dynamically
            $customer->total_debt = $customer->total_debt; // ✅ Include total_debt dynamically
        });

        return response()->json($customers, 200);
    }

    // ✅ Only Admins can create customers
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }


        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:customers',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'vat_code' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'city' => 'nullable|string',
            'tax_code' => 'nullable|string'
        ]);

        $customer = Customer::create($request->all());

        return response()->json(['message' => 'Customer created successfully', 'customer' => $customer], 201);
    }

    // ✅ Allow Admin & Staff to view a single customer
    public function show($id)
    {
        $customer = Customer::with('orders')->find($id);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $customer->total_orders = $customer->total_orders; // ✅ Include total_orders dynamically
        $customer->total_debt = $customer->total_debt; // ✅ Include total_debt dynamically

        return response()->json($customer, 200);
    }

    // ✅ Only Admins can update customers
    public function update(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }


        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|string',
            'email' => 'sometimes|string|email|unique:customers,email,' . $customer->id,
            'address' => 'sometimes|string',
            'phone' => 'sometimes|string',
            'vat_code' => 'sometimes|string',
            'postal_code' => 'sometimes|string',
            'city' => 'sometimes|string',
            'tax_code' => 'sometimes|string' // ✅ No need to validate tax_code if not provided
        ]);

        $customer->update($request->all());

        return response()->json(['message' => 'Customer updated successfully', 'customer' => $customer], 200);
    }

    // ✅ Only Admins can delete a customer
    public function destroy($id)
    {
        if (Auth::user()->role !== 'admin' && Auth::user()->role !== 'manager') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $customer = Customer::find($id);

        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }

        $customer->delete();

        return response()->json(['message' => 'Customer deleted successfully'], 200);
    }
}
