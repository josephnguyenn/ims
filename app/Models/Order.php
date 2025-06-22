<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrderProduct;
use App\Models\DeliverySupplier;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id','delivery_supplier_id','cashier_id','paid_amount',
        'subtotal_czk','tip_czk','grand_total_czk','rounded_total_czk',
        'payment_currency','amount_tendered_czk','amount_tendered_eur',
        'change_due_czk','change_due_eur', 'payment_method','source',
        'shift_id' // ✅ thêm dòng này
    ];
        
    protected $appends = ['total_price']; // ✅ Ensure total_price is in JSON response

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function deliverySupplier()
    {
        return $this->belongsTo(DeliverySupplier::class);
    }

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }

    // ✅ Auto-calculate total price dynamically
    public function getTotalPriceAttribute()
    {
        return $this->orderProducts()->sum(DB::raw('price * quantity'));
    }

    // ✅ Do NOT attempt to save total_price (Fix update issue)
    public function updateTotalPrice()
    {
        $this->saveQuietly(); // ✅ Only saves other fields, not total_price
    }
}
