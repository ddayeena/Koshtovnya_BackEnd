<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Mail\BanMail;
use App\Models\Review;
use App\Models\ReviewReply;
use App\Services\Product\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ReviewReplyController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $id,  ReviewService $review_service)
    {
        $user = $request->user();
        if ($user->access === 0 || $user->is_permanently_banned || $user->banned_until) {
            return response()->json([
                'message' => 'Вам заборонено залишати коментарі через порушення правил.'
            ], 403);
        }

        //Check if the product exists
        $review = Review::findOrFail($id);

        //Check if the authenticated user has role use
        if($request->user()->role === "user"){
            return response()->json(['message' => 'Only admin can reply to reviews.',],403);
        }

        //Check if the data are correct
        $validated = $request->validate([
            'comment' => 'required|string|max:1000',
        ]);
        if ($review_service->hasBadWords($validated['comment'])) {
            return $review_service->handleBadContent($user);
        }

        //Create review
        $reply = ReviewReply::create([
            'admin_id' => $request->user()->id,
            'review_id' => $review->id,
            'comment' => $validated['comment'],
        ]);

        return response()->json([
            'message' => 'Reply to review added successfully.',
            'reply' => $reply,
        ]);
    }

}
