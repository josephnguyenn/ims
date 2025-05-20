<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{

    public $timestamps = false;
    protected $fillable = ['name','visible_in_pos'];

    // optional, if you ever need it:
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

}
