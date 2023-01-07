<?php

namespace App\Http\Controllers;
use App\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    public function store(Request $request, $movie_id)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'comment' => 'required',
            'user_id' => 'required'
            
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];
            return response()->json($response, 404);
        }

        $data = array(
            'comment'       =>   $request->comment,
            'user_id'     =>   $request->user_id,
            'movie_id'  =>   $request->movie_id
           
        );

        $comments = Comment::create($data);

        $data = $comments->toArray();

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'Comment stored successfully.'
        ];

        return response()->json($response, 200);
    }
}
