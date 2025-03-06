<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Order;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'address', 'phone', 'vat_code']; // ❌ No 'total_orders' column

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // ✅ Automatically calculate total orders (DYNAMICALLY)
    public function getTotalOrdersAttribute()
    {
        return $this->orders()->count();
    }

    // ✅ Automatically calculate total debt
    public function getTotalDebtAttribute()
    {
        return $this->orders()->sum(\DB::raw('(SELECT SUM(price * quantity) FROM order_products WHERE order_products.order_id = orders.id)')) 
            - $this->orders()->sum('paid_amount');
    }

    // ✅ Ensure total_orders & total_debt appear in JSON response
    protected $appends = ['total_orders', 'total_debt'];
}
