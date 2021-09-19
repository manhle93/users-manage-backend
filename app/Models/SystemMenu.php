<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemMenu extends Model
{
    protected $guarded = [];
    public $timestamps = false;
    public function roles()
    {
        return $this->belongsToMany('App\Models\Role', 'role_menus', 'menu_id');
    }
    public function children()
    {
        return $this->hasMany('App\Models\SystemMenu', 'parent_id')->orderBy('order', "ASC");
    }
    public function parent()
    {
        return $this->belongsTo('App\Models\SystemMenu', 'parent_id');
    }
    // public function scopeMenu($query)
    // {
    //     return $query->whereNull('parent_id');
    // } 
}
