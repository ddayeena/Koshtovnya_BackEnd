<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public function productDescription()
    {
        return $this->belongsTo(ProductDescription::class, 'product_description_id');
    }
}
