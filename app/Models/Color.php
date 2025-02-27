<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    protected $table = 'colors';
    use HasFactory;

    public function products()
    {
        return $this->belongsToMany(Product::class)->withTimestamps();
    }
}
