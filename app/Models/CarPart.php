<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarPart extends Model
{
    protected $fillable = [
        'name',
        'category',
        'price',
        'stock_quantity',
    ];
}
