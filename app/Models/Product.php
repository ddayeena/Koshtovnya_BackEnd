<?php

namespace App\Models;

use App\Models\Traits\HasFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    use HasFactory;
    use HasFilter;
    protected $fillable = [
        'name',
        'price',
        'image_url',
        'image_public_id',
        'product_description_id',
    ];
    
    protected $casts = [
        'price' => 'float',
    ];

    public function productDescription()
    {
        return $this->belongsTo(ProductDescription::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class)->withPivot('quantity')->withTimestamps();
    }

    public function fittings()
    {
        return $this->belongsToMany(Fitting::class,'fitting_product')->withPivot('quantity','material_id')->withTimestamps();
    }

    public function colors()
    {
        return $this->belongsToMany(Color::class)->withTimestamps();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function wishlists()
    {
        return $this->belongsToMany(Wishlist::class)->withTimestamps()->withTimestamps();
    }

    public function carts()
    {
        return $this->belongsToMany(Cart::class)->withPivot('quantity')->withTimestamps();
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}
