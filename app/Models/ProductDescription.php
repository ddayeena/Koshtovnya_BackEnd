<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDescription extends Model
{
    protected $table = 'product_descriptions';

    use HasFactory;
    protected $fillable = [
        'bead_producer_id',
        'weight',
        'country_of_manufacture',
        'type_of_bead',
        'category_id',
    ];

    public function beadProducer()
    {
        return $this->belongsTo(BeadProducer::class);
    }

    public function product()
    {
        return $this->hasOne(Product::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}

