<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fitting extends Model
{
    protected $table = 'fittings';
    use HasFactory;

    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('material_id', 'quantity')->withTimestamps();
    }

    public function material()
    {
        return $this->belongsTo(Material::class, 'material_id');
    }

}
