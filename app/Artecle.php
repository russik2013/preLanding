<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Artecle extends Model
{
    protected $fillable = ['title', 'content'];

    public function getLinks()
    {
        return $this->morphMany('App\LinkParams', 'params');
    }
}
