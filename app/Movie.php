<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
 
  
    protected $guarded = [];
    public function author()
    {
        return $this->belongsTo('App\Author');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function genre()
    {
        return $this->belongsTo('App\Genre');
    }

    public function comments(){
        
        return $this->hasMany('App\Comment');
    }
}
