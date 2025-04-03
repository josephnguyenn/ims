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
        'tax',
        'expired_date',
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
            $product->actual_quantity = $product->original_quantity;
            $product->total_cost = $product->original_quantity * $product->cost;
    
            // ✅ Only fallback to shipment expiry if not set
            if (!$product->expired_date) {
                $shipment = Shipment::find($product->shipment_id);
                if ($shipment) {
                    $product->expired_date = $shipment->expired_date;
                }
            }
        });
    
        static::updating(function ($product) {
            $product->total_cost = $product->original_quantity * $product->cost;
    
            // ✅ Same here: only fallback if still null
            if (!$product->expired_date) {
                $shipment = Shipment::find($product->shipment_id);
                if ($shipment) {
                    $product->expired_date = $shipment->expired_date;
                }
            }
        });
    }
    
}
