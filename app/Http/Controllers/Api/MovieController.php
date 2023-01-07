<?php

namespace App\Http\Controllers\Api;
use Auth;
use App\Movie;
use DB;
use Response;
use File;
use Carbon\Carbon;
use \PDF;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $movies = DB::table('movies')
                        ->leftjoin('authors', 'movies.author_id', '=', 'authors.id')
                        ->leftjoin('genres', 'movies.genre_id', '=', 'genres.id')
                        ->select(
                            'movies.id',
                            'movies.title',
                            'movies.summary',
                            'movies.cover_photo',
                            'authors.name as author_name',
                            'genres.name as genre_name',
                            'movies.tags',
                            'movies.rating',
                        )
                        ->orderBy('movies.created_at', 'DESC')
                        ->paginate(7);

            foreach($movies as $movie){
                $data[] = [
                    'id' => $movie->id,
                    'title' => $movie->title,
                    'summary' => $movie->summary,
                    'cover_image' => asset('images/' . $movie->cover_photo),
                    'author' => $movie->author_name,
                    'genre' => $movie->genre_name,
                    'tag' => $movie->tags,
                    'rating' => $movie->rating,
                ];
            }

        return Response::json($movies, 200);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'title' => 'required',
            'summary' => 'required',
            'cover_photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'user_id' => 'required',
            'genre_id' => 'required',
            'author_id' => 'required',
            'tags' => 'required',
            'rating' => 'required'
        ]);

        if ($validator->fails()) {
            $response = [
                'success' => false,
                'data' => 'Validation Error.',
                'message' => $validator->errors()
            ];
            return response()->json($response, 404);
        }

        if ($image = $request->file('cover_photo')) {
            $destinationPath = 'image/';
            $profileImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
            $image->move($destinationPath, $profileImage);
            $photo = "$profileImage";
        }

        $data = array(
            'title'       =>   $request->title,
            'summary'     =>   $request->summary,
            'cover_photo'  =>   $photo,
            'user_id'      =>   Auth::user()->id,
            'genre_id'     =>   $request->genre_id,
            'author_id'    =>   $request->author_id,
            'tags'     =>   $request->tags,
            'rating'     =>   $request->rating,
        );

        $movies = Movie::create($data);

        $data = $movies->toArray();

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'Movie stored successfully.'
        ];

        return response()->json($response, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $movie = Movie::find($id);
        // dd($movie);
        if(!isset($movie)){
            return $this->errorResponse('Movie Not Found!', 400);
        }
        $comments = $movie->comments()->select('id','comment')->get()->toArray();

        $data = [
            'title' => $movie->title,
            'summary' => $movie->summary,
            'cover_image' => ($movie->cover_image != null) ? asset('images/' . $movie->cover_image) : asset('images/default.jpg'),
            'author' => $movie->author->name,
            'genre' => $movie->genre->name,
            'ratings' => $movie->rating,
            'tags' => $movie->tags,
            'comments' => $comments
        ];

        $filename = "movie_".$movie->id.'_'.Carbon::now()->toDateString();
        $path = storage_path('pdf');

        if(!File::exists($path)) {
            File::makeDirectory($path, $mode = 0777, true, true);
        } 

        $pdf = PDF::loadView('myPDF', $data)->save(''.$path.'/'.$filename.'.pdf');
        $data['pdf'] = asset('/storage/pdf/'.$filename.'.pdf');

        return $this->successResponse($data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // dd($request->all());
        $request->validate([
            'title' => 'required',
            'summary' => 'required',
            // 'cover_photo' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'user_id' => 'required',
            'genre_id' => 'required',
            'author_id' => 'required',
            'tags' => 'required',
            'rating' => 'required'
        ]);

        $movie = Movie::find($id);
        $movie->title = $request->title;
        $movie->summary = $request->summary;
        $movie->cover_photo = ($request->cover_photo) ? $request->cover_photo: 'NULL';
        $movie->user_id = Auth::user()->id;
        $movie->author_id = $request->author_id;
        $movie->genre_id = $request->genre_id;
        $movie->save();
    
        $data = $movie->toArray();

        $response = [
            'success' => true,
            'data' => $data,
            'message' => 'Movie updated successfully.'
        ];

        return response()->json($response, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $authUser = Auth::user();

        $movie = Movie::find($id);

        if(!isset($movie)){
            return $this->errorResponse('Movie Not Found!', 400);
        }

        if($authUser->id == $movie->user_id) {
            $result = $movie->delete();
            $response = [
                'success' => true,
                'message' => 'Movie deleted successfully.'
            ];
    
            return response()->json($response, 200);

        }else{
            $response = [
                'success' => false,
                'message' => 'You have no access to delete'
            ];
    
            return response()->json($response, 400);
        }
    }
}
