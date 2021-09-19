<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NhomTo extends Model
{
    protected $guarded = [];

    public function phongBan()
    {
        return $this->belongsTo('App\Models\PhongBan', 'phong_ban_id');
    }
}
