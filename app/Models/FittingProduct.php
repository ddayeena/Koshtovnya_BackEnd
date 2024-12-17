<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FittingProduct extends Model
{
    protected $table = 'fitting_product';
    use HasFactory;

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function fitting()
    {
        return $this->belongsTo(Fitting::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }
}
