<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class DanhMuc extends Model
{
    protected $guarded = [];

    public function children()
    {
        return $this->hasMany('App\Models\DanhMuc', 'parent_id');
    }
}
