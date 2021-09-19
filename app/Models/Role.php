<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $guarded = [];
    public $timestamps = false;
    public function menus()
    {
        return $this->belongsToMany('App\Models\SystemMenu', 'role_menus');
    }
    public function user()
    {
        return $this->hasMany('App\Models\User', 'role_id');
    }
}
