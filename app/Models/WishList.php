<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    protected $table = 'wishlists'; 
    use HasFactory;
    protected $guarded = false;

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
    
}
