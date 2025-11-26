<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $source = $this->input('source', 'admin');

        if ($source === 'pos') {
            return $this->posRules();
        }

        return $this->adminRules();
    }

    /**
     * Validation rules for POS orders
     */
    protected function posRules(): array
    {
        return [
            'cashier_id' => 'required|exists:users,id',
            'subtotal_czk' => 'required|numeric|min:0',
            'tip_czk' => 'nullable|numeric|min:0',
            'tip_eur' => 'nullable|numeric|min:0',
            'grand_total_czk' => 'required|numeric|min:0',
            'rounded_total_czk' => 'required|numeric|min:0',
            'payment_currency' => 'required|in:CZK,EUR',
            'amount_tendered_czk' => 'required_if:payment_currency,CZK|numeric|min:0',
            'amount_tendered_eur' => 'required_if:payment_currency,EUR|numeric|min:0',
            'change_due_czk' => 'nullable|numeric',
            'change_due_eur' => 'nullable|numeric',
            'payment_method' => 'required|in:cash,card,transfer',
            'items' => 'required|array|min:1',
            'items.*.code' => 'required|string|exists:products,code',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
        ];
    }

    /**
     * Validation rules for admin orders
     */
    protected function adminRules(): array
    {
        return [
            'customer_id' => 'required|exists:customers,id',
            'delivery_supplier_id' => 'nullable|exists:delivery_suppliers,id',
            'total_amount' => 'required|numeric|min:0',
            'paid_amount' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:1000',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.price' => 'required|numeric|min:0',
        ];
    }
}
