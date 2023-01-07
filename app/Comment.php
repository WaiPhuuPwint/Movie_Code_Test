<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table = 'comments';

    protected $fillable = [
        'comment', 'user_id', 'movie_id',     
    ];

    public function movie()
    {
        return $this->belongsTo('App\Movie');
    }
}
