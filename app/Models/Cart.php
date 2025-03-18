<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $table = 'carts';
    use HasFactory;
    protected $guarded = false;

    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('quantity','size')->withTimestamps();
    }
}
