<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Product extends Model
{
    public $expiry_mode = null; //memory only not from db
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'original_quantity',
        'price',
        'cost',
        'category_id',
        'shipment_id',
        'tax',
        'expired_date',
        'is_weighted',
    ]; // ❌ actual_quantity, total_cost, expired_date are NOT fillable

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    // ✅ Automatically set actual_quantity, total_cost, and expired_date before saving
    protected static function boot()
    {
        parent::boot();
    
        static::creating(function ($product) {
            $product->actual_quantity = $product->original_quantity;
            $product->total_cost = $product->original_quantity * $product->cost;
        
            // Only apply expiry fallback if this comes from form and explicitly set
            if (isset($product->expiry_mode) && $product->expiry_mode === 'inherit' && !$product->expired_date) {
                $shipment = Shipment::find($product->shipment_id);
                if ($shipment) {
                    $product->expired_date = $shipment->expired_date;
                }
            }
        });
        
        static::updating(function ($product) {
            $product->total_cost = $product->original_quantity * $product->cost;
        
            if (isset($product->expiry_mode) && $product->expiry_mode === 'inherit' && !$product->expired_date) {
                $shipment = Shipment::find($product->shipment_id);
                if ($shipment) {
                    $product->expired_date = $shipment->expired_date;
                }
            }
        });
        
    }
    
}
