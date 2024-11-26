<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    use HasFactory;

    public function productDescription()
    {
        return $this->belongsTo(ProductDescription::class);
    }
    
    public function orders()
    {
        return $this->belongsToMany(Order::class);
    }

    public function fitting()
    {
        return $this->belongsToMany(Fitting::class);
    }
    
    public function sizes()
    {
        return $this->belongsToMany(Size::class);
    }

    public function colors()
    {
        return $this->belongsToMany(Color::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function wishlists()
    {
        return $this->belongsToMany(Wishlist::class);
    }

}
