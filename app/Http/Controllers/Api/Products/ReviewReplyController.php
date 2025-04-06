<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\ReviewReply;
use App\Services\Product\ReviewService;
use Illuminate\Http\Request;

class ReviewReplyController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $id,  ReviewService $review_service)
    {
        //Check if the product exists
        $review = Review::findOrFail($id);

        //Check if the authenticated user has role use
        if($request->user()->role == "user"){
            return response()->json(['message' => 'Only admin can reply to reviews.',],403);
        }

        //Check if the data are correct
        $validated = $request->validate([
            'comment' => 'required|string|max:1000',
        ]);
        //Filter bad words
        $filteredComment = $review_service->filterBadWords($validated['comment']);

        //Create review
        $reply = ReviewReply::create([
            'admin_id' => $request->user()->id,
            'review_id' => $review->id,
            'comment' => $filteredComment,
        ]);

        return response()->json([
            'message' => 'Reply to review added successfully.',
            'reply' => $reply,
        ]);
    }

}
