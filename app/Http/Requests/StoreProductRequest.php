<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:100|unique:products,code',
            'original_quantity' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'category_id' => 'required|exists:product_categories,id',
            'shipment_id' => 'required|exists:shipments,id',
            'tax' => 'nullable|numeric|min:0|max:100',
            'expired_date' => 'nullable|date|after:today',
            'expiry_mode' => 'required|in:custom,inherit,none',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'code.unique' => 'A product with this code already exists.',
            'expired_date.after' => 'Expiry date must be in the future.',
            'category_id.exists' => 'The selected category does not exist.',
            'shipment_id.exists' => 'The selected shipment does not exist.',
        ];
    }
}
