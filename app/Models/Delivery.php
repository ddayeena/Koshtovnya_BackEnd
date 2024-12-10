<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    protected $table = 'deliveries';
    use HasFactory;
    protected $fillable = [
        'order_id',
        'city',
        'delivery_address',
        'cost',
        'delivery_type_id'
    ];


    public function deliveryType()
    {
        return $this->belongsTo(DeliveryType::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
