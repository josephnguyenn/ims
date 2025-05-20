<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index()
    {
        // Only return categories visible in POS
        $categories = ProductCategory::select('id', 'name', 'visible_in_pos')
            ->get();

        return response()->json($categories, 200);
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:100',
            'visible_in_pos' => 'required|boolean',
        ]);

        $category = ProductCategory::create($data);

        return response()->json($category, 201);
    }

    /**
     * Display the specified category.
     */
    public function show($id)
    {
        $category = ProductCategory::findOrFail($id);
        return response()->json($category, 200);
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, $id)
    {
        $category = ProductCategory::findOrFail($id);

        $data = $request->validate([
            'name'           => 'sometimes|required|string|max:100',
            'visible_in_pos' => 'sometimes|required|boolean',
        ]);

        $category->update($data);

        return response()->json($category, 200);
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy($id)
    {
        $category = ProductCategory::findOrFail($id);
        $category->delete();

        return response()->json(null, 204);
    }
}
