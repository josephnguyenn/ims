<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_supplier_id',
        'storage_id',
        'order_date',
        'received_date',
        'expired_date',
    ]; // ❌ 'cost' is NOT fillable because it's calculated

    public function supplier()
    {
        return $this->belongsTo(ShipmentSupplier::class, 'shipment_supplier_id');
    }

    public function storage()
    {
        return $this->belongsTo(Storage::class, 'storage_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    // ✅ Automatically calculate total cost
    public function calculateTotalCost()
    {
        $this->cost = $this->products()->sum('total_cost'); // ✅ Sum of all product total_cost
        $this->save();
    }
}
