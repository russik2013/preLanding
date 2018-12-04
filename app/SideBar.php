<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SideBar extends Model
{
    protected $fillable = ['text', 'url', 'photo', 'side_bar_groop_id', 'profit', 'people'];

    public function setPhotoAttribute($value)
    {
        $filename = md5(time())."_".time().'.'.$value->getClientOriginalExtension();
        $value->move(public_path().'\images\\', $filename);
        $this->attributes['photo'] =  $filename;
    }

    public function groop()
    {
        return $this->belongsTo(SideBarGroop::class, 'side_bar_groop_id', 'id');
    }
}
