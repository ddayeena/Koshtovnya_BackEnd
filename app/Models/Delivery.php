<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    protected $table = 'deliveries';
    use HasFactory;

    public function deliveryType()
    {
        return $this->belongsTo(DeliveryType::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
}
