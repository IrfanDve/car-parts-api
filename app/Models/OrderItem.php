<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 
        'car_part_id', 
        'quantity',
        'total_price'
    ];
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function carPart()
    {
        return $this->belongsTo(CarPart::class);
    }
}
