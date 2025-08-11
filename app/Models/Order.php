<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'order_date',
        'delivery_date',
        'status',
    ];

    protected $dates = [
        'order_date',
        'delivery_date',
    ];

    protected $casts = [
        'order_date'    => 'datetime',
        'delivery_date' => 'datetime',
    ];

}
