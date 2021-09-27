<?php

namespace App\models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $guarded = [];
    protected $appends = ['thoi_gian'];
    public function userComment()
    {
        return $this->belongsTo('App\Models\User', 'from_user_id', 'id');
    }
    public function getThoiGianAttribute()
    {
        Carbon::setLocale('vi');
        return Carbon::parse($this->attributes['created_at'])->setTimezone('Asia/Ho_Chi_Minh')->diffForHumans();
    }
}
