<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\TopLatestReviewResource;
use App\Mail\BanMail;
use App\Models\Product;
use App\Models\Review;
use App\Services\Product\ReviewService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index($productId)
    {
        $product = Product::findOrFail($productId);
        $reviews = $product->reviews()->paginate(3);
        return ReviewResource::collection($reviews);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $id, ReviewService $review_service)
    {
        $user = $request->user();
        if ($user->access === 0 || $user->is_permanently_banned || $user->banned_until) {
            return response()->json([
                'message' => 'Вам заборонено залишати коментарі через порушення правил.'
            ], 403);
        }
        
        //Check if the product exists
        $product = Product::findOrFail($id);

        //Check if the data are correct
        $validated = $request->validate([
            'comment' => 'required|string|max:1000',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        // Перевірка на погані слова
        if ($review_service->hasBadWords($validated['comment'])) {
            $user->warnings_count += 1;

            if ($user->warnings_count >= 3) {
                if ($user->was_banned_before) {
                    $user->is_permanently_banned = true;
                    $user->banned_until = null;
                    $user->access = 0;
                } else {
                    $user->banned_until = now()->addDays(3);
                    $user->was_banned_before = true;
                    $user->access = 0;
                }
            
                Mail::to($user->email)->send(new BanMail($user));
                $user->warnings_count = 0;
                $user->save();

                $request->user()->tokens()->delete();
            
                return response()->json([
                    'message' => $user->is_permanently_banned
                        ? 'Вас заблоковано назавжди за повторне порушення правил.'
                        : 'Вас заблоковано на 3 дні за порушення правил.'
                ], 403);
            }
            

            $user->save();

            return response()->json([
                'message' => "Ваш коментар містить заборонені слова. Будь ласка, дотримуйтеся правил. Попередження {$user->warnings_count} з 3."
            ], 422);
        }


        //Create review
        $review = Review::create([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
            'comment' => $validated['comment'],
            'rating' => $validated['rating'],
        ]);

        return response()->json([
            'message' => 'Review added successfully.',
            'review' => $review,
        ]);
    }

    public function topLatest()
    {
        $reviews = Review::whereIn('rating', [4, 5])
            ->latest()
            ->take(10)
            ->get();

        return TopLatestReviewResource::collection($reviews);
    }
}
