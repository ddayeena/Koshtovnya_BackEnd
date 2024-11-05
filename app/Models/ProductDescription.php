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
}
