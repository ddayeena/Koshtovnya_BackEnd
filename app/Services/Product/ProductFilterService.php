<?php

namespace App\Services\Product;

use App\Http\Filter\ProductFilter;
use App\Models\BeadProducer;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductDescription;
use App\Models\ProductVariant;

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
