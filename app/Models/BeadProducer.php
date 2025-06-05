<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeadProducer extends Model
{
    protected $table = 'bead_producers';
    use HasFactory;

    public function productDescriptions()
    {
        return $this->hasMany(ProductDescription::class);
    }

    public function translations()
    {
        return $this->hasMany(BeadProducerTranslation::class);
    }

    public function translation($locale = null)
    {
        $locale = $locale ?? app()->getLocale();
        return $this->translations->where('locale', $locale)->first();
    }
}
