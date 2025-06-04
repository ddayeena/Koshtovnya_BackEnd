<?php

namespace App\Http\Filter;

use Illuminate\Database\Eloquent\Builder;

class ProductFilter extends AbstractFilter
{
    const IS_AVAILABLE = 'is_available';
    const SIZE_FROM = 'size_from';
    const SIZE_TO = 'size_to';
    const COLOR = 'color';
    const TYPE_OF_BEAD = 'type_of_bead';
    const BEAD_PRODUCER = 'bead_producer';
    const WEIGHT_FROM = 'weight_from';
    const WEIGHT_TO = 'weight_to';
    const PRICE_FROM = 'price_from';
    const PRICE_TO = 'price_to';
    const CATEGORY = 'category';
    const RATING = 'rating';
    const IS_DELETED = 'is_deleted';

    public function getCallbacks(): array
    {
        return [
            self::IS_AVAILABLE => 'isAvailable',
            self::SIZE_FROM => 'sizeFrom',
            self::SIZE_TO => 'sizeTo',
            self::COLOR => 'color',
            self::TYPE_OF_BEAD => 'typeOfBead',
            self::BEAD_PRODUCER => 'beadProducer',
            self::WEIGHT_FROM => 'weightFrom',
            self::WEIGHT_TO => 'weightTo',
            self::PRICE_FROM => 'priceFrom',
            self::PRICE_TO => 'priceTo',
            self::CATEGORY => 'category',
            self::RATING => 'rating',
            self::IS_DELETED => 'isDeleted'
        ];
    }

    public function category(Builder $builder, $value)
    {
        $builder->whereHas('productDescription', function ($query) use ($value) {
            $query->withTrashed()->whereHas('category.translations', function ($query) use ($value) {
                $query->whereIn('name', (array) $value);
            });
        });
    }
    
    public function isAvailable(Builder $builder, $value)
    {
        if (in_array(0, (array)$value)) {
            $builder->whereDoesntHave('productVariants', function ($query) {
                $query->withTrashed()->where('quantity', '>', 0);
            });
        }
    
        if (in_array(1, (array)$value)) {
            $builder->whereHas('productVariants', function ($query) {
                $query->withTrashed()->where('quantity', '>', 0);
            });
        }
    }
    public function isDeleted(Builder $builder, $value)
    {
        $value = (array) $value; 
    
        if (in_array(0, $value) && in_array(1, $value)) {
            return;
        }
    
        if (in_array(1, $value)) {
            $builder->onlyTrashed();
        } elseif (in_array(0, $value)) {
            $builder->whereNull('deleted_at');
        }
    }

    public function sizeFrom(Builder $builder, $value)
    {
        $builder->whereHas('productVariants', function ($query) use ($value) {
            $query->withTrashed()->where('size', '>=', $value);
        });
    }
    
    public function sizeTo(Builder $builder, $value)
    {
        $builder->whereHas('productVariants', function ($query) use ($value) {
            $query->withTrashed()->where('size', '<=', $value);
        });
    }
    

    public function color(Builder $builder, $value)
    {
        $builder->whereHas('colors.translations', function ($query) use ($value) {
            $query->where('color_name', $value);
        });
    }

    public function rating(Builder $builder, $values)
    {
        $builder->where(function ($query) use ($values) {
            foreach ($values as $value) {
                $min = (float) $value;
                $max = $min + 1;
    
                $query->orWhereRaw('(
                    SELECT AVG(rating)
                    FROM reviews
                    WHERE reviews.product_id = products.id AND deleted_at IS NULL
                ) >= ?', [$min])
                ->whereRaw('(
                    SELECT AVG(rating)
                    FROM reviews
                    WHERE reviews.product_id = products.id AND deleted_at IS NULL
                ) < ?', [$max]);
            }
        });
    }
    
    
    public function beadProducer(Builder $builder, $value)
    {
        $builder->whereHas('productDescription', function ($query) use ($value) {
            $query->withTrashed()->whereHas('beadProducer', function ($query) use ($value) {
                $query->whereIn('origin_country', (array) $value);
            });
        });
    }
    
    public function typeOfBead(Builder $builder, $value)
    {
        $builder->whereHas('productDescription', function ($query) use ($value) {
            $query->withTrashed()->whereIn('type_of_bead', (array) $value);
        });
    }
    

    public function weightFrom(Builder $builder, $value)
    {
        $builder->whereHas('productDescription', function ($query) use ($value) {
            $query->withTrashed()->where('weight', '>=', $value);
        });
    }
    
    public function weightTo(Builder $builder, $value)
    {
        $builder->whereHas('productDescription', function ($query) use ($value) {
            $query->withTrashed()->where('weight', '<=', $value);
        });
    }
    

    public function priceFrom(Builder $builder, $value)
    {
        $builder->where('price', '>=', $value);
    }

    public function priceTo(Builder $builder, $value)
    {
        $builder->where('price', '<=', $value);
    }
}
