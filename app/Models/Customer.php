<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'address', 'phone', 'vat_code']; // ❌ No 'total_orders' column

    protected $appends = ['total_orders', 'total_debt']; // ✅ Ensure these attributes are included in JSON response

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // ✅ Automatically calculate total orders (DYNAMICALLY)
    public function getTotalOrdersAttribute()
    {
        return $this->orders()->count();
    }

    // ✅ Fix: Proper calculation of total_debt
    public function getTotalDebtAttribute()
    {
        return $this->orders()
            ->join('order_products', 'orders.id', '=', 'order_products.order_id') // ✅ Join orders with order_products
            ->sum(DB::raw('order_products.price * order_products.quantity')) // ✅ Sum all product prices in orders
            - $this->orders()->sum('paid_amount'); // ✅ Subtract total paid amount
    }
}
