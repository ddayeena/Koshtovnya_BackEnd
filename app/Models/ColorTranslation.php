<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ColorTranslation extends Model
{
    public $timestamps = false;
    protected $table = 'color_translations';

    protected $fillable = ['color_id', 'locale', 'color_name'];

    public function color()
    {
        return $this->belongsTo(Color::class);
    }
}

