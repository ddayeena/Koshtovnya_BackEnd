<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeadProducer extends Model
{
    protected $table = 'bead_producers';
    use HasFactory;

    public function productDescription()
    {
        return $this->hasMany(ProductDescription::class);
    }
}
