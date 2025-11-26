<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer' => $this->whenLoaded('customer', function () {
                return [
                    'id' => $this->customer->id,
                    'name' => $this->customer->name,
                    'phone' => $this->customer->phone,
                ];
            }),
            'delivery_supplier' => $this->whenLoaded('deliverySupplier', function () {
                return [
                    'id' => $this->deliverySupplier->id,
                    'name' => $this->deliverySupplier->name,
                ];
            }),
            'total_amount' => (float) $this->total_amount,
            'paid_amount' => (float) $this->paid_amount,
            'remaining_amount' => (float) ($this->total_amount - $this->paid_amount),
            'note' => $this->note,
            'grand_total_czk' => $this->grand_total_czk ? (float) $this->grand_total_czk : null,
            'cashier_id' => $this->cashier_id,
            'payment_method' => $this->payment_method,
            'products' => OrderProductResource::collection($this->whenLoaded('orderProducts')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
