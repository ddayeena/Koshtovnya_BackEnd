<?php

namespace App\Http\Filter;

use Illuminate\Database\Eloquent\Builder;
class ProductFilter extends AbstractFilter
{
    const IS_AVAILABLE = 'is_available';
    const SIZE = 'size';
    const COLOR ='color';
    const TYPE_OF_BEAD = 'type_of_bead';
    const BEAD_PRODUCER = 'bead_producer';
    const WEIGHT_FROM = 'weight_from';
    const WEIGHT_TO = 'weight_to';
    const PRICE_FROM = 'price_from';
    const PRICE_TO = 'price_to';

    public function getCallbacks(): array
    {
        return [
            self::IS_AVAILABLE => 'isAvailable',
            self::SIZE => 'size',
            self::COLOR => 'color',
            self::TYPE_OF_BEAD => 'typeOfBead',
            self::BEAD_PRODUCER => 'beadProducer',
            self::WEIGHT_FROM => 'weightFrom',
            self::WEIGHT_TO => 'weightTo',
            self::PRICE_FROM => 'priceFrom',
            self::PRICE_TO => 'priceTo',
        ];
    }

    public function isAvailable(Builder $builder, $value)
    {
        if (in_array(0, (array)$value)) {
            $builder->where('quantity', '=', 0);
        }
        
        if (in_array(1, (array)$value)) {
            $builder->where('quantity', '>', 0);
        }
    }        

    public function size(Builder $builder, $value)
    {
        $builder->whereHas('sizes', function ($query) use ($value) {
            $query->where('size_value', $value);
        });
    }

    public function color(Builder $builder, $value)
    {
        $builder->whereHas('colors', function ($query) use ($value) {
            $query->where('color_name', $value);
        });
    }

    public function typeOfBead(Builder $builder, $value)
    {
        $builder->whereHas('productDescription', function ($query) use ($value) {
            $query->whereIn('type_of_bead', (array) $value);
        });
    }

    public function beadProducer(Builder $builder, $value)
    {
        $builder->whereHas('productDescription.beadProducer', function ($query) use ($value) {
            $query->whereIn('origin_country', (array) $value);
        });
    }

    public function weightFrom(Builder $builder, $value)
    {
        $builder->whereHas('productDescription', function ($query) use ($value) {
            $query->where('weight','>', $value);
        });
    }

    public function weightTo(Builder $builder, $value)
    {
        $builder->whereHas('productDescription', function ($query) use ($value) {
            $query->where('weight','<',  $value);
        });
    }

    public function priceFrom(Builder $builder, $value)
    {
        $builder->where('price','>',$value);
    }

    public function priceTo(Builder $builder, $value)
    {
        $builder->where('price','<',$value);
    }


}