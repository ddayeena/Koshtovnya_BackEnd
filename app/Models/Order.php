<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    use HasFactory;
    protected $fillable = [
        'user_id',
        'status', 
        'total_amount',
        'last_name',
        'first_name',
        'second_name',
        'phone_number',
        'status'
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('quantity');
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
