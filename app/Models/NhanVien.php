<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

class NhanVien extends Model
{
    protected $guarded = [];

    public function phongBan()
    {
        return $this->belongsTo('App\Models\PhongBan', 'phong_ban_id');
    }
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }
    public function chucVu()
    {
        return $this->belongsTo('App\Models\DanhMuc', 'chuc_vu_id');
    }
    public function nhomTo()
    {
        return $this->belongsTo('App\Models\NhomTo', 'nhom_to_id');
    }
}
