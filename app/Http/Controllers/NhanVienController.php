<?php

namespace App\Http\Controllers;

use App\Http\Resources\NhanVienResource;
use App\models\NhanVien;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class NhanVienController extends Controller
{
    public function getNhanVien(Request $request)
    {
        $page = $request->get('page', 1);
        $per_pager = $request->get('perPage', 5);
        $search = $request->get('search', null);
        $query = NhanVien::with('phongBan', 'user', 'chucVu', 'nhomTo');
        if ($search != null) {
            $search = trim($search);
            $query->where('name', 'ilike', "%{$search}%")
                ->orWhere('code', 'ilike', "%{$search}%")
                ->orWhere('email', 'ilike', "%{$search}%")
                ->orWhere('so_dien_thoai', 'ilike', "%{$search}%");
        }

        $data = $query->orderBy('updated_at', 'DESC')->paginate($per_pager, ['*'], 'page', $page);
        return NhanVienResource::collection($data);
    }
    public function addNhanVien(Request $request)
    {
        $nhanVien = $request->only(
            'name',
            'ngay_sinh',
            'gioi_tinh',
            'noi_sinh',
            'dia_chi_hien_tai',
            'so_dien_thoai',
            'so_cmt',
            'noi_cap',
            'ngay_vao_cong_ty',
            'trinh_do_chuyen_mon',
            'chuyen_nganh',
            'ma_so_thue',
            'tai_khoan_ngan_hang',
            'ngan_hang',
            'active',
            'phong_ban_id',
            'chuc_vu_id',
            'nhom_to_id',
            'ghi_chu'
        );
        $userNhanVien = $request->only('user_name', 'email', 'name', 'url_image');
        $validator =  Validator::make($nhanVien, [
            'name' => 'required',
            'phong_ban_id' => 'required',
        ]);
        $validatorUser =  Validator::make($userNhanVien, [
            'user_name' => 'required',
            'email' => 'required',
        ]);
        if ($validator->fails() || $validatorUser->fails()) {
            return response()->json([
                'message' => __('入力した内容に不備があります。入力項目を確認してください。'),
                'data' => [
                    $validatorUser->errors()->all(),
                    $validator->errors()->all()
                ]
            ], 400);
        }
        $checkEmail = User::where('email', $userNhanVien['email'])->first();
        $checkUserName = User::where('user_name', $userNhanVien['user_name'])->first();
        if ($checkEmail) {
            return response(['message' => 'メールアドレスは既に存在しています。 !'], 401);
        }
        if ($checkUserName) {
            return response(['message' => 'ログイン用のユーザ名は既に存在しています。 !'], 401);
        }
        try {
            DB::beginTransaction();
            $userNhanVien['password'] = Hash::make(12345678);
            $userNhanVien['role_id'] = 4;
            $user = User::create($userNhanVien);
            $nhanVien['user_id'] = $user->id;
            NhanVien::create($nhanVien);
            DB::commit();
            return response(['message' => 'Success'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return $e;
            return response(['message' => 'Không thể thêm người dùng'], 500);
        }
    }

    public function editNhanVien(Request $request)
    {
        $nhanVien = $request->only(
            'id',
            'name',
            'ngay_sinh',
            'gioi_tinh',
            'noi_sinh',
            'dia_chi_hien_tai',
            'so_dien_thoai',
            'so_cmt',
            'noi_cap',
            'ngay_vao_cong_ty',
            'trinh_do_chuyen_mon',
            'chuyen_nganh',
            'ma_so_thue',
            'tai_khoan_ngan_hang',
            'ngan_hang',
            'active',
            'phong_ban_id',
            'chuc_vu_id',
            'nhom_to_id',
            'ghi_chu'
        );
        $userNhanVien = $request->only('user_name', 'email', 'user_id', 'name');
        $validator =  Validator::make($nhanVien, [
            'name' => 'required',
            'phong_ban_id' => 'required',
            'id' => 'required',
        ]);
        $validatorUser =  Validator::make($userNhanVien, [
            'name' => 'required',
            'user_name' => 'required',
            'email' => 'required',
            'user_id' => 'required'
        ]);
        if ($validator->fails() || $validatorUser->fails()) {
            return response()->json([
                'message' => __('入力した内容に不備があります。入力項目を確認してください。'),
                'data' => [
                    $validator->errors()->all(),
                    $validatorUser->errors()->all(),
                ]
            ], 400);
        }
        $checkEmail = User::where('email', $userNhanVien['email'])->where('id', '<>', $userNhanVien['user_id'])->first();
        $checkUserName = User::where('user_name', $userNhanVien['user_name'])->where('id', '<>', $userNhanVien['user_id'])->first();
        if ($checkEmail) {
            return response(['message' => 'メールアドレスは既に存在しています。 !'], 401);
        }
        if ($checkUserName) {
            return response(['message' => 'ログイン用のユーザ名は既に存在しています。 !'], 401);
        }
        try {
            DB::beginTransaction();
            NhanVien::find($nhanVien['id'])->update($nhanVien);
            User::find($userNhanVien['user_id'])->update(['email' => $userNhanVien['email'], 'user_name' => $userNhanVien['user_name']]);
            DB::commit();
            return response(['message' => 'Success'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response(['message' => 'Không thể thêm người dùng'], 500);
        }
    }

    public function showNhanVien(Request $request)
    {
        $nhanVienId = $request->nhanVienId;
        if (!$nhanVienId) return [];
        $data = NhanVien::where('id', $nhanVienId)->with('phongBan', 'user', 'chucVu', 'nhomTo')->first();
        return new NhanVienResource($data);
    }
}
