<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Links extends Model
{
    protected $fillable = ['name'];

    public function getParamsArticles()
    {
        return $this->hasMany('App\LinkParams', 'link_id', 'id')
            ->where('params_type', 'App\Artecle');
    }

    public function getParamsSideBarGroups()
    {
        return $this->hasMany('App\LinkParams', 'link_id', 'id')
            ->where('params_type', 'App\SideBarGroop');
    }

    public function allParams()
    {
        return $this->hasMany('App\LinkParams', 'link_id', 'id');
    }
}
