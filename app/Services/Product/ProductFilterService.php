<?php

namespace App\Services\Product;

use App\Http\Filter\ProductFilter;
use App\Models\BeadProducer;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductDescription;
use App\Models\ProductVariant;
use App\Models\Review;

class ProductFilterService
{
    private $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    //Return filtered products
    public function getFilteredProducts(array $filters, $user, $isAdminPanel, $products = null)
    {
        // Create filter
        $filter = app()->make(ProductFilter::class, ['params' => $filters]);
    
        if ($products instanceof \Illuminate\Database\Eloquent\Builder) {
            $productQuery = $products;
        } else {
            $productQuery = Product::query();
        }
    
        if ($isAdminPanel || isset($filters['is_deleted'])) {
            $productQuery->withTrashed();
        }
    
        $productQuery = $productQuery->filter($filter);
    
        $productQuery->with([
            'productDescription' => function ($query) use ($isAdminPanel, $filters) {
                if ($isAdminPanel || isset($filters['is_deleted'])) {
                    $query->withTrashed();
                } else {
                    $query->whereNull('deleted_at');
                }
            }
        ]);
    
        $products = $productQuery->paginate(15);
    
        // Attach info
        $products = $this->productService->attachWishlistInfo($products, $user);
        $products = $this->productService->attachCartInfo($products, $user);
    
        return $products;
    }
    
    
    //Return filter
    public function getFilter()
    {
        return [
            'Доступність' => $this->getAvailabilityFilter(),
            'Розмір' => $this->getSizeFilter(),
            'Колір' => $this->getColorFilter(),
            'Тип бісеру' => $this->getTypeOfBeadFilter(),
            'Виробник бісеру' => $this->getBeadProducerFilter(),
            'Вага' => $this->getWeightFilter(),
            'Ціна' => $this->getPriceFilter(),
            'Рейтинг' => $this->getRatingFilter(),
            'Категорія' => $this->getCategory(),
            'Статус' => $this->getDeletedFilter()
        ];
    }

    // Availabilty filter
    private function getAvailabilityFilter()
    {
        return [
            ['name' => 'Немає в наявності', 'count' => Product::whereDoesntHave('productVariants', function ($query) {
                $query->where('quantity', '>', 0);
            })->count()],

            ['name' => 'В наявності', 'count' => Product::whereHas('productVariants', function ($query) {
                $query->where('quantity', '>', 0);
            })->count()],
        ];
    }

    // Size filter
    private function getSizeFilter()
    {
        return [
            'min' => ProductVariant::min('size'),
            'max' => ProductVariant::max('size'),
        ];
    }

    // Color filter
    private function getColorFilter()
    {
        return Color::pluck('color_name');
    }

    // Type of bead filter
    private function getTypeOfBeadFilter()
    {
        return [
            ['name' => 'Матовий', 'count' => ProductDescription::where('type_of_bead', 'Матовий')->count()],
            ['name' => 'Прозорий', 'count' => ProductDescription::where('type_of_bead', 'Прозорий')->count()],
        ];
    }

    // Bead producer filter
    private function getBeadProducerFilter()
    {
        return BeadProducer::withCount('productDescriptions')
            ->get()
            ->map(function ($producer) {
                return [
                    'origin_country' => $producer->origin_country,
                    'count' => $producer->product_descriptions_count,
                ];
            });
    }

    // Weight filter
    private function getWeightFilter()
    {
        return [
            'min' => ProductDescription::min('weight'),
            'max' => ProductDescription::max('weight'),
        ];
    }

    //Price filter
    private function getPriceFilter()
    {
        return [
            'min' => Product::min('price'),
            'max' => Product::max('price'),
        ];
    }

    //Category filter
    private function getCategory()
    {
        return Category::withCount('productDescriptions')->pluck('name');
    }

    private function getRatingFilter()
    {
        $products = \App\Models\Product::withAvg(['reviews as avg_rating' => function ($q) {
            $q->whereNull('deleted_at');
        }], 'rating')->get();
    
        $buckets = [
            '1' => 0,
            '2' => 0,
            '3' => 0,
            '4' => 0,
            '5' => 0,
        ];
    
        foreach ($products as $product) {
            $rating = $product->avg_rating;
    
            if ($rating >= 1 && $rating < 2) {
                $buckets['1']++;
            } elseif ($rating >= 2 && $rating < 3) {
                $buckets['2']++;
            } elseif ($rating >= 3 && $rating < 4) {
                $buckets['3']++;
            } elseif ($rating >= 4 && $rating < 5) {
                $buckets['4']++;
            } elseif ($rating == 5) {
                $buckets['5']++;
            }
        }
    
        return collect($buckets)->map(function ($count, $name) {
            return ['name' => $name, 'count' => $count];
        })->values()->all();
    }
    

    // Deleted products filter
    private function getDeletedFilter()
    {
        return [
            [
                'name' => 'Видалено',
                'count' => Product::onlyTrashed()->count(), 
            ],
            [
                'name' => 'Не видалено',
                'count' => Product::whereNull('deleted_at')->count(), 
            ],
        ];
    }
    
}
