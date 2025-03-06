<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\OrderProduct;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['customer_id', 'paid_amount'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }

    // ✅ Calculate total price dynamically from order products
    public function getTotalPriceAttribute()
    {
        return $this->orderProducts()->sum(\DB::raw('price * quantity'));
    }

    // ✅ Auto-update total price when order products change
    public function updateTotalPrice()
    {
        $this->save();
    }
}
