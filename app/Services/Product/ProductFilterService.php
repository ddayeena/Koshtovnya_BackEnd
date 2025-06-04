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
use App\Services\ExchangeRateService;
use Illuminate\Database\Eloquent\Builder;

class ProductFilterService
{
    private $productService;
    private $exchange_rate_service;

    public function __construct(ProductService $productService, ExchangeRateService $exchange_rate_service)
    {
        $this->productService = $productService;
        $this->exchange_rate_service = $exchange_rate_service;
    }

    //Return filtered products
    public function getFilteredProducts(array $filters, $user, $isAdminPanel, $products = null)
    {
        // Create filter
        $filter = app()->make(ProductFilter::class, ['params' => $filters]);

        if ($products instanceof Builder) {
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

        $products = $productQuery->paginate(16);

        // Attach info
        $products = $this->productService->attachWishlistInfo($products, $user);
        $products = $this->productService->attachCartInfo($products, $user);

        return $products;
    }


    //Return filter
    public function getFilter($categoryId = null, $currency = 'uah', $locale = 'uk')
    {
        return [
            'Доступність' => $this->getAvailabilityFilter($categoryId),
            'Розмір' => $this->getSizeFilter($categoryId),
            'Колір' => $this->getColorFilter($categoryId),
            'Тип бісеру' => $this->getTypeOfBeadFilter($categoryId),
            'Виробник бісеру' => $this->getBeadProducerFilter($categoryId),
            'Вага' => $this->getWeightFilter($categoryId),
            'Ціна' => $this->getPriceFilter($categoryId, $currency),
            'Рейтинг' => $this->getRatingFilter($categoryId),
            'Категорія' => $this->getCategory($locale),
            'Статус' => $this->getDeletedFilter($categoryId)
        ];
    }


    // Availabilty filter
    private function getAvailabilityFilter($categoryId = null)
    {
        $availableQuery = Product::whereHas('productVariants', function ($query) {
            $query->where('quantity', '>', 0);
        });

        $notAvailableQuery = Product::whereDoesntHave('productVariants', function ($query) {
            $query->where('quantity', '>', 0);
        });

        if ($categoryId) {
            $availableQuery->whereHas('productDescription', fn($q) => $q->where('category_id', $categoryId));
            $notAvailableQuery->whereHas('productDescription', fn($q) => $q->where('category_id', $categoryId));
        }

        return [
            ['name' => 'Немає в наявності', 'count' => $notAvailableQuery->count()],
            ['name' => 'В наявності', 'count' => $availableQuery->count()],
        ];
    }


    // Size filter
    private function getSizeFilter($categoryId = null)
    {
        $query = ProductVariant::query();

        if ($categoryId) {
            $query->whereHas('product.productDescription', fn($q) => $q->where('category_id', $categoryId));
        }

        return [
            'min' => $query->min('size'),
            'max' => $query->max('size'),
        ];
    }


    // Color filter
    private function getColorFilter()
    {
        return Color::pluck('color_name');
    }

    // Type of bead filter
    private function getTypeOfBeadFilter($categoryId = null)
    {
        $query = ProductDescription::query();

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return [
            ['name' => 'Матовий', 'count' => (clone $query)->where('type_of_bead', 'Матовий')->count()],
            ['name' => 'Прозорий', 'count' => (clone $query)->where('type_of_bead', 'Прозорий')->count()],
        ];
    }


    // Bead producer filter
    private function getBeadProducerFilter($categoryId = null)
    {
        $query = BeadProducer::withCount(['productDescriptions' => function ($q) use ($categoryId) {
            if ($categoryId) {
                $q->where('category_id', $categoryId);
            }
        }]);

        return $query->get()->map(function ($producer) {
            return [
                'origin_country' => $producer->origin_country,
                'count' => $producer->product_descriptions_count,
            ];
        });
    }


    // Weight filter
    private function getWeightFilter($categoryId = null)
    {
        $query = ProductDescription::query();

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        return [
            'min' => $query->min('weight'),
            'max' => $query->max('weight'),
        ];
    }


    //Price filter
    private function getPriceFilter($categoryId = null, $currency = 'uah')
    {
        $query = Product::query();
    
        if ($categoryId) {
            $query->whereHas('productDescription', fn($q) => $q->where('category_id', $categoryId));
        }
    
        $min = $query->min('price');
        $max = $query->max('price');
    
        if ($currency === 'usd') {
            $rate = $this->exchange_rate_service->getUsdRate(); 
            $min = round($min / $rate, 2);
            $max = round($max / $rate, 2);
        }
    
        return [
            'min' => (string)$min,
            'max' => (string)$max,
            'currency' => $currency,
        ];
    }
    


    //Category filter
    private function getCategory($locale = 'ukr')
    {
        return Category::with(['translations' => function ($query) use ($locale) {
            $query->where('locale', $locale);
        }])
        ->withCount('productDescriptions')
        ->get()
        ->map(function ($category) use ($locale) {
            $translation = $category->translations->first();
            return $translation ? $translation->name : $category->name;
        });
    }
    

    private function getRatingFilter($categoryId = null)
    {
        $productsQuery = Product::withAvg(['reviews as avg_rating' => fn($q) => $q->whereNull('deleted_at')], 'rating');

        if ($categoryId) {
            $productsQuery->whereHas('productDescription', fn($q) => $q->where('category_id', $categoryId));
        }

        $products = $productsQuery->get();

        $buckets = [
            '1' => 0,
            '2' => 0,
            '3' => 0,
            '4' => 0,
            '5' => 0,
        ];

        foreach ($products as $product) {
            $rating = $product->avg_rating;

            if ($rating >= 1 && $rating < 2) $buckets['1']++;
            elseif ($rating >= 2 && $rating < 3) $buckets['2']++;
            elseif ($rating >= 3 && $rating < 4) $buckets['3']++;
            elseif ($rating >= 4 && $rating < 5) $buckets['4']++;
            elseif ($rating == 5) $buckets['5']++;
        }

        return collect($buckets)->map(fn($count, $name) => ['name' => $name, 'count' => $count])->values()->all();
    }



    // Deleted products filter
    private function getDeletedFilter($categoryId = null)
    {
        $deletedQuery = Product::onlyTrashed();
        $activeQuery = Product::whereNull('deleted_at');
    
        if ($categoryId) {
            $deletedQuery->whereHas('productDescription', fn($q) => $q->where('category_id', $categoryId));
            $activeQuery->whereHas('productDescription', fn($q) => $q->where('category_id', $categoryId));
        }
    
        return [
            ['name' => 'Видалено', 'count' => $deletedQuery->count()],
            ['name' => 'Не видалено', 'count' => $activeQuery->count()],
        ];
    }
    
}
