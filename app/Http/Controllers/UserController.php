<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function createUser(Request $request)
    {
        $data = $request->only('email', 'name', 'user_name', 'role_id', 'password', 'url_image', 'password', 'id');
        $confirmPassword = $request->confirmPassword;
        $validator =  Validator::make($data, [
            'name' => 'required',
            'user_name' => 'required',
            'email' => 'required',
            'password' => 'required',
            'role_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => __('Dữ liệu không hợp lệ'),
                'data' => [
                    $validator->errors()->all()
                ]
            ], 400);
        }
        $checkEmail = User::where('email', $data['email'])->first();
        $checkUserName = User::where('user_name', $data['user_name'])->first();
        if ($checkEmail) {
            return response(['message' => 'Email đã tồn tại !'], 401);
        }
        if ($checkUserName) {
            return response(['message' => 'Tên đăng nhập (User name) đã tồn tại !'], 401);
        }
        if ($data['password'] !==  $confirmPassword) {
            return response(['message' => 'Mật khẩu 2 lần nhập không trùng khớp'], 402);
        }
        $data['password'] = Hash::make($data['password']);
        try {
            User::create($data);
            return response(['message' => 'Success'], 200);
        } catch (Exception $e) {
            return response(['message' => 'Không thể tạo người dùng'], 500);
        }
    }

    public function getUsers(Request $request)
    {
        $page = $request->get('page', 1);
        $per_pager = $request->get('perPage', 10);
        $search = $request->get('search', null);
        $role_id = $request->get('role_id', null);
        $status = $request->get('trang_thai', null);
        $query = User::with('role');
        if ($search != null) {
            $search = trim($search);
            $query->where('name', 'ilike', "%{$search}%")->orWhere('email', 'ilike', "%{$search}%")->orWhere('user_name', 'ilike', "%{$search}%");
        }
        if ($role_id != null) {
            $query->where('role_id', $role_id);
        }

        if ($status != null) {
            $query->where('active', $status);
        }
        
        $data = $query->orderBy('updated_at', 'DESC')->paginate($per_pager, ['*'], 'page', $page);
        return $data;
    }

    public function updateUser(Request $request)
    {
        $data = $request->only('email', 'name', 'user_name', 'role_id', 'password', 'url_image', 'password', 'id');
        $confirmPassword = $request->confirmPassword;
        $validator = Validator::make($data, [
            'id' => 'required',
            'name' => 'required',
            'email' => 'required',
            'user_name' => 'required',
            'role_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => __('Dữ liệu không hợp lệ'),
                'data' => [
                    $validator->errors()->all()
                ]
            ], 400);
        }
        $checkEmail = User::where('email', $data['email'])->where('id', '<>', $data['id'])->first();
        $checkUserName = User::where('user_name', $data['user_name'])->where('id', '<>', $data['id'])->first();
        if ($checkEmail) {
            return response(['message' => 'Email đã tồn tại !'], 401);
        }
        if ($checkUserName) {
            return response(['message' => 'Tên đăng nhập (User name) đã tồn tại !'], 401);
        }
        if (isset($data['password']) && $data['password']) {
            if ($data['password'] !== $confirmPassword) {
                return response(['message' => 'Mật khẩu 2 lần nhập không trùng khớp'], 402);
            }
            $data['password'] = Hash::make($data['password']);
        }
        try {
            User::find($data['id'])->update($data);
        } catch (\Exception $e) {
            return response(['message' => 'Không thể cập nhật người dùng']);
        }
    }
    public function updateMyUser(Request $request){
        $data = $request->only('email', 'name', 'user_name', 'company_name');
        $validator = Validator::make($data, [
            'name' => 'required',
            'email' => 'required',
            'user_name' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => __('Dữ liệu không hợp lệ'),
                'data' => [
                    $validator->errors()->all()
                ]
            ], 400);
        }
        $user = Auth::user();
        $checkEmail = User::where('email', $data['email'])->where('id', '<>', $user->id)->first();
        $checkUserName = User::where('user_name', $data['user_name'])->where('id', '<>', $user->id)->first();
        if ($checkEmail) {
            return response(['message' => 'Email đã tồn tại !'], 401);
        }
        if ($checkUserName) {
            return response(['message' => 'Tên đăng nhập (User name) đã tồn tại !'], 401);
        }
        try {
            User::find($user->id)->update($data);
        } catch (\Exception $e) {
            return response(['message' => 'Không thể cập nhật người dùng']);
        }
    }
    public function activeDeactive(Request $request)
    {
        $data = $request->all();
        $validator =  Validator::make($data, [
            'userId' => 'required',
            'active' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => __('Dữ liệu không hợp lệ'),
                'data' => [
                    $validator->errors()->all()
                ]
            ], 400);
        }
        try {
            User::find($data['userId'])->update(['active' => $data['active']]);
            return response(['message' => 'Success'], 200);
        } catch (\Exception $e) {
            return response(['message' => 'Không thể cập nhật'], 500);
        }
    }

    public function uploadAvatarProfile(Request $request)
    {
        if ($request->file) {
            $image = $request->file;
            try {
                $checkImage = getimagesize($image);
                $path = $image->getClientOriginalName();
                $paths = explode(".", $path);
                $ext = strtolower(end($paths));
                if (!in_array($ext, ["png", "jpg", "gif", "jpeg"])) {
                    return response()->json(['message' => 'File không hợp lệ'], 403);
                }
                if ($checkImage != false) {
                    $fileArray = array('image' => $image);
                    $rules = array(
                        'image' => 'mimes:jpeg,jpg,png,gif|required|max:20000' // max 10000kb
                    );
                    $validator = Validator::make($fileArray, $rules);
                    if ($validator->fails()) {
                        return response()->json(['message' => 'File không hợp lệ!'], 400);
                    }
                    $name = rand(100000,9999999) . time() . '.' . $image->getClientOriginalExtension();
                    $image->storeAs('public/images/avatar/', $name);

                    $user = User::find(Auth::user()->id);
                    $user->update(['url_image' => 'storage/images/avatar/' . $name]);
                    return 'storage/images/avatar/' . $name;
                }
                return response()->json(['message' => 'File không hợp lệ'], 403);
            } catch (\Exception $e) {
                return $e;
                return response()->json(['message' => 'File không hợp lệ'], 403);
            }
        } else {
            return response()->json(['message' => 'File không tồn tại'], 404);
        }
    }
    public function uploadAvatarManagement(Request $request)
    {
        if ($request->file) {
            $image = $request->file;
            try {
                $checkImage = getimagesize($image);
                $path = $image->getClientOriginalName();
                $paths = explode(".", $path);
                $ext = strtolower(end($paths));
                if (!in_array($ext, ["png", "jpg", "gif", "jpeg"])) {
                    return response()->json(['message' => 'File không hợp lệ'], 403);
                }
                if ($checkImage != false) {
                    $fileArray = array('image' => $image);
                    $rules = array(
                        'image' => 'mimes:jpeg,jpg,png,gif|required|max:20000' // max 10000kb
                    );
                    $validator = Validator::make($fileArray, $rules);
                    if ($validator->fails()) {
                        return response()->json(['message' => 'File không hợp lệ!'], 400);
                    }
                    $name = rand(100000,9999999) . time() . '.' . $image->getClientOriginalExtension();
                    $image->storeAs('public/images/avatar/', $name);
                    return 'storage/images/avatar/' . $name;
                }
                return response()->json(['message' => 'File không hợp lệ'], 403);
            } catch (\Exception $e) {
                return $e;
                return response()->json(['message' => 'File không hợp lệ'], 403);
            }
        } else {
            return response()->json(['message' => 'File không tồn tại'], 404);
        }
    }

    public function logOutAll()
    {
        $user = User::find(Auth::user()->id);
        if (!$user || !$user->tokens) {
            return response(['message' => 'Chưa có thiết bị nào đăng nhập'], 200);
        }
        $tokensArr = json_decode($user->tokens);
        try {
            foreach ($tokensArr as $item) {
                try {
                    $tk =  Auth::setToken($item)->getToken();
                    Auth::invalidate($tk);
                } catch (\Exception $e) {}
            }
            $user->update(['tokens' => null]);
            return response(['message' => 'Đã đăng xuất trên tất cả các thiết bị'], 200);
        } catch (\Exception $e) {
            return response(['message' => 'Không thể đăng xuất trên tất cả các thiết bị'], 500);
        }
    }

    public function changePassword(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'currentPass' => 'required',
            'newPassWord' => 'required',
            'reNewPassWord' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'message' => 'Dữ liệu không hợp lệ',
                'data' => [
                    $validator->errors()->all()
                ]
            ], 400);
        }
        $oldPassword = $data['currentPass'];
        $newPassword = $data['newPassWord'];
        $reNewPasswork = $data['reNewPassWord'];
        if (!Hash::check($oldPassword, Auth::user()->password)) {  //Mảng băm Hash 
            return response()->json([
                'message' => 'Mật khẩu hiện tại không chính xác',
                'code' => 400,
                'data' => ''
            ], 400);
        };
        if ($newPassword == $oldPassword) {
            return response()->json([
                'message' => 'Mật khẩu mới trùng mật khẩu hiện tại',
                'code' => 400,
                'data' => ''
            ], 400);
        };
        if ($newPassword != $reNewPasswork) {
            return response()->json([
                'message' => 'Mật khẩu 2 lần nhập không khớp',
                'code' => 400,
                'data' => ''
            ], 400);
        };
        try {
            $user = User::find(Auth::user()->id);
            $user->update(['password' => Hash::make($newPassword)]);
            $this->logOutAll();
            return response()->json([
                'message' => 'Cập nhật mật khẩu thành công',
                'code' => 200,
                'data' => ''
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Lỗi cập nhật',
                'code' => 500,
                'data' => $e
            ], 500);
        }
    }
}
