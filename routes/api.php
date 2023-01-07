<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('register', 'API\PassportAuthController@register');
Route::post('login', 'API\PassportAuthController@login');


Route::get('/movies', 'API\MovieController@index');
Route::get('/movies/{id}', 'API\MovieController@show');

Route::middleware('auth:api')->group(function () {
    // Route::resource('movies', 'API\MovieController');

    Route::post('/movies', 'API\MovieController@store');
    Route::post('/movies/{id}', 'API\MovieController@update');
    Route::delete('/movies/{id}', 'API\MovieController@destroy');


    Route::post('/movies/{movie_id}/comments', 'CommentController@store');
 
});
