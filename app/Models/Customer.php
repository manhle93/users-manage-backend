<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $guarded = [];
    public function comments()
    {
        return $this->hasMany('App\Models\Comment', 'to_user_id', 'user_id');
    }
    public function industry()
    {
        return $this->belongsTo('App\Models\Lookup', 'industry_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
}
