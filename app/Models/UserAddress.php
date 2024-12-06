<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'delivery_type_id', 'city','post_office','delivery_address'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deliveryType()
    {
        return $this->belongsTo(DeliveryType::class);
    }

    public function userAddress()
    {
        return $this->hasOne(UserAddress::class);
    }
}
