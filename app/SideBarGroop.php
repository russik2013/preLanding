<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SideBarGroop extends Model
{
    protected $fillable = ['name'];

    public function items()
    {
        return $this->hasMany(SideBar::class);
    }

    public function getLinks()
    {
        return $this->morphMany('App\LinkParams', 'params');
    }
}
