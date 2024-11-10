<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDescription extends Model
{
    use HasFactory;

    public function beadProducer()
    {
        return $this->belongsTo(BeadProducer::class, 'bead_producer_id');
    }

    public function product()
    {
        return $this->hasOne(Product::class, 'id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function fitting(){
        return $this->belongsToMany(Fitting::class, 'product_fittings', 'product_id', 'fitting_id');
    }

    public function sizes()
    {
        return $this->belongsToMany(Size::class, 'product_sizes', 'product_description_id', 'size_id');
    }

    public function colors()
    {
        return $this->belongsToMany(Color::class, 'product_colors', 'product_description_id', 'color_id');
    }

}

