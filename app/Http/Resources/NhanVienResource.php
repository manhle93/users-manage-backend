<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class NhanVienResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'ngay_sinh' => $this->ngay_sinh,
            'gioi_tinh' => $this->gioi_tinh,
            'noi_sinh' => $this->noi_sinh,
            'dia_chi_hien_tai' => $this->dia_chi_hien_tai,
            'so_dien_thoai' => $this->so_dien_thoai,
            'so_cmt' => $this->so_cmt,
            'noi_cap' => $this->noi_cap,
            'ngay_vao_cong_ty' => $this->ngay_vao_cong_ty,
            'trinh_do_chuyen_mon' => $this->trinh_do_chuyen_mon,
            'chuyen_nganh' => $this->chuyen_nganh,
            'ma_so_thue' => $this->ma_so_thue,
            'tai_khoan_ngan_hang' => $this->tai_khoan_ngan_hang,
            'ngan_hang' => $this->ngan_hang,
            'active' => $this->active,
            'phong_ban_id' => $this->phong_ban_id,
            'chuc_vu_id' => $this->chuc_vu_id,
            'nhom_to_id' => $this->nhom_to_id,
            'ghi_chu' => $this->ghi_chu,
            'url_image' => $this->user ? $this->user->url_image : '',
            'user_name' => $this->user ? $this->user->user_name : '',
            'ghi_chu' => $this->ghi_chu,
            'email' =>  $this->user ? $this->user->email : '',
            'phong_ban' =>  $this->phongBan ? $this->phongBan->name : '',
            'chuc_vu' =>  $this->chucVu ? $this->chucVu->name : '',
            'nhom_to' =>  $this->nhomTo ? $this->nhomTo->name : '',
            'user_id' => $this->user ? $this->user->id : null,
        ];
    }
}
