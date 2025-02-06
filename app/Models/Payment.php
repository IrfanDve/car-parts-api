<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 
        'amount',
        'transaction_id',
        'payment_method',
        'status',
    ];
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
