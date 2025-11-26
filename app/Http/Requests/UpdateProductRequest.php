<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['admin', 'manager']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $productId = $this->route('product');

        return [
            'name' => 'sometimes|string|max:255',
            'code' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('products', 'code')->ignore($productId),
            ],
            'original_quantity' => 'sometimes|integer|min:0',
            'price' => 'sometimes|numeric|min:0',
            'cost' => 'sometimes|numeric|min:0',
            'category_id' => 'sometimes|exists:product_categories,id',
            'shipment_id' => 'sometimes|exists:shipments,id',
            'tax' => 'sometimes|numeric|min:0|max:100',
            'expired_date' => 'nullable|date',
            'expiry_mode' => 'required|in:custom,inherit,none',
        ];
    }
}
