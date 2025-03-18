<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';
    use HasFactory;
    protected $fillable = [
        'order_id',
        'payment_method',
        'amount',
        'paid_at',
        'status'
    ];
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
