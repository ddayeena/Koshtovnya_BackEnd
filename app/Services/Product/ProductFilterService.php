<?php

namespace App\Services\Product;

use App\Http\Filter\ProductFilter;
use App\Models\BeadProducer;
use App\Models\Category;
use App\Models\Color;
use App\Models\ColorTranslation;
use App\Models\Product;
use App\Models\ProductDescription;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Services\ExchangeRateService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;

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
            'Колір' => $this->getColorFilter($categoryId, $locale),
            'Тип бісеру' => $this->getTypeOfBeadFilter($categoryId),
            'Виробник бісеру' => $this->getBeadProducerFilter($categoryId),
            'Вага' => $this->getWeightFilter($categoryId),
            'Ціна' => $this->getPriceFilter($categoryId, $currency),
            'Рейтинг' => $this->getRatingFilter($categoryId),
            'Категорія' => $this->getCategory($locale),
            'Статус' => $this->getDeletedFilter($categoryId)
        ];
    }

    // Type of bead filter
    private function getTypeOfBeadFilter($categoryId = null, $locale = 'uk')
    {
        $query = ProductDescription::query();
    
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
    
        $types = ['Матовий', 'Прозорий'];
        $result = [];
    
        foreach ($types as $type) {
            $translated = __('product.type_of_bead.' . $type);
            $count = (clone $query)->where('type_of_bead', $type)->count();
            $result[] = [
                'name' => $translated,
                'count' => $count
            ];
        }
    
        return $result;
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

        $labels = [
            'uk' => ['unavailable' => 'Немає в наявності', 'available' => 'В наявності'],
            'en' => ['unavailable' => 'Unavailable', 'available' => 'Available'],
        ];

        // Вибираємо переклади залежно від локалі, або fallback на 'uk'
        $translated = $labels[App::getLocale()] ?? $labels['uk'];

        return [
            ['name' => $translated['unavailable'], 'count' => $notAvailableQuery->count()],
            ['name' => $translated['available'], 'count' => $availableQuery->count()],
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
    private function getColorFilter($categoryId, $locale = 'uk')
    {
        return Color::getNamesByLocale($locale);
    }

    //Category filter
    private function getCategory($locale = 'uk')
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

    // Bead producer filter
    private function getBeadProducerFilter($categoryId = null)
    {
        $locale = request('lang', app()->getLocale());

        $query = BeadProducer::with(['translations'])
            ->withCount(['productDescriptions' => function ($q) use ($categoryId) {
                if ($categoryId) {
                    $q->where('category_id', $categoryId);
                }
            }]);

        return $query->get()->map(function ($producer) use ($locale) {
            $translation = $producer->translation($locale);

            return [
                'origin_country' => $translation ? $translation->origin_country : $producer->origin_country,
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
        $labels = [
            'uk' => ['deleted' => 'Видалено', 'notdeleted' => 'Не видалено'],
            'en' => ['deleted' => 'Deleted', 'notdeleted' => 'Not deleted'],
        ];

        // Вибираємо переклади залежно від локалі, або fallback на 'uk'
        $translated = $labels[App::getLocale()] ?? $labels['uk'];

        return [
            ['name' => $translated['deleted'], 'count' => $deletedQuery->count()],
            ['name' => $translated['notdeleted'], 'count' => $activeQuery->count()],
        ];
    }
}
