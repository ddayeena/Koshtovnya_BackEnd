<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    protected $fillable = [
        'image_url',
        'image_public_id'
    ];
    use HasFactory;

    public function productDescriptions()
    {
        return $this->hasMany(ProductDescription::class);
    }

    public function translations()
    {
        return $this->hasMany(CategoryTranslation::class);
    }

    public function translation($locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        return $this->translations->where('locale', $locale)->first();
    }

    public function getTranslatedNameAttribute()
    {
        return $this->translation()?->name ?? $this->name;
    }
}
