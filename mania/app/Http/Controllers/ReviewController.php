<?php

namespace App\Http\Controllers;

// use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;

class ReviewController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'video_id' => 'required|exists:videos,id',
            'stars' => 'required|integer|min:1|max:5',
        ]);

        $review = Review::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'video_id' => $data['video_id'],
            ],
            [
                'stars' => $data['stars'],
            ]
        );

        return response()->json($review);
    }
}
