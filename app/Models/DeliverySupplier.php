<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliverySupplier extends Model
{
    use HasFactory;

    protected $fillable = ['name']; // ✅ Add this line
}
