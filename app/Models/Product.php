<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'original_quantity',
        'price',
        'cost',
        'category',
        'shipment_id',
        'tax'
    ]; // ❌ actual_quantity, total_cost, expired_date are NOT fillable

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    // ✅ Automatically set actual_quantity, total_cost, and expired_date before saving
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            $product->actual_quantity = $product->original_quantity; // ✅ Set actual_quantity = original_quantity
            $product->total_cost = $product->original_quantity * $product->cost; // ✅ Auto-calculate total_cost

            // ✅ Set expired_date from linked shipment
            $shipment = Shipment::find($product->shipment_id);
            if ($shipment) {
                $product->expired_date = $shipment->expired_date;
            }
        });

        static::updating(function ($product) {
            $product->total_cost = $product->original_quantity * $product->cost; // ✅ Recalculate if quantity or cost changes

            // ✅ Ensure expired_date stays in sync with shipment
            $shipment = Shipment::find($product->shipment_id);
            if ($shipment) {
                $product->expired_date = $shipment->expired_date;
            }
        });
    }
}
