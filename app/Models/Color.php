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

    public function translations()
    {
        return $this->hasMany(ColorTranslation::class);
    }

    public function translation($locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        return $this->translations->where('locale', $locale)->first();
    }

    public static function getNamesByLocale($locale): array
    {
        return self::with(['translations' => function ($query) use ($locale) {
            $query->where('locale', $locale);
        }])->get()->map(function ($color) {
            return optional($color->translations->first())->color_name;
        })->filter()->values()->all();
    }
}
