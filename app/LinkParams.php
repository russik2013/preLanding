<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LinkParams extends Model
{
    protected $fillable = ["link_id"];
    public function params()
    {
        return $this->morphTo();
    }
    public function link()
    {
        return $this->belongsTo(Links::class,'link_id','id');
    }

}
