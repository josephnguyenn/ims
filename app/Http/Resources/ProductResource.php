<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'name' => $this->name,
            'code' => $this->code,
            'price' => (float) $this->price,
            'cost' => (float) $this->cost,
            'tax' => $this->tax ? (float) $this->tax : null,
            'original_quantity' => $this->original_quantity,
            'actual_quantity' => $this->actual_quantity,
            'total_cost' => (float) $this->total_cost,
            'expired_date' => $this->expired_date,
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
            'shipment' => $this->whenLoaded('shipment', function () {
                return [
                    'id' => $this->shipment->id,
                    'order_date' => $this->shipment->order_date,
                    'expired_date' => $this->shipment->expired_date,
                ];
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
