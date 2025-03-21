<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'total_price',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function carts() {
        return $this->hasMany(Cart::class);
    }


    public function products()
    {
        return $this->hasManyThrough(Product::class, Cart::class, 'order_id', 'id', 'id', 'product_id');
    }
}
