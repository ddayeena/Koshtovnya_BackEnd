<?php

namespace App\Http\Controllers\Api\Products;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\ReviewReply;
use Illuminate\Http\Request;

class ReviewReplyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $id)
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

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
