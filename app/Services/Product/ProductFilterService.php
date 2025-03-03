<?php

namespace App\Services\Product;

use App\Http\Filter\ProductFilter;
use App\Models\BeadProducer;
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
    public function getFilteredProducts(array $filters, $user)
    {
        // Create filter
        $filter = app()->make(ProductFilter::class, ['params' => $filters]);

        // Get filtered products and paginate them
        $productQuery = Product::filter($filter);
        $products = $productQuery->paginate(15);

        // Attach information about cart and wishlist
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
}
