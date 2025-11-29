<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id', 'delivery_supplier_id', 'cashier_id', 'paid_amount',
        'subtotal_czk', 'tip_czk', 'tip_eur', 'grand_total_czk', 'rounded_total_czk',
        'payment_currency', 'amount_tendered_czk', 'amount_tendered_eur',
        'change_due_czk', 'change_due_eur', 'payment_method', 'source',
        'shift_id', // ✅ thêm dòng này
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

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }

    // ✅ Auto-calculate total price dynamically from loaded relationship
    public function getTotalPriceAttribute()
    {
        // Use loaded relationship if available, otherwise query database
        if ($this->relationLoaded('orderProducts')) {
            return $this->orderProducts->sum(function($op) {
                return $op->price * $op->quantity;
            });
        }
        return $this->orderProducts()->sum(DB::raw('price * quantity'));
    }

    // ✅ Do NOT attempt to save total_price (Fix update issue)
    public function updateTotalPrice()
    {
        $this->saveQuietly(); // ✅ Only saves other fields, not total_price
    }
}
