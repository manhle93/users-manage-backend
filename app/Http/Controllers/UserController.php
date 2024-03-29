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
        $data = $request->only('email', 'name', 'user_name', 'role_id', 'password', 'url_image', 'password', 'id', 'company_name');
        $confirmPassword = $request->confirmPassword;
        $validator =  Validator::make($data, [
            'name' => 'required',
            'user_name' => 'required',
            'email' => 'required',
            'password' => 'required|min:8|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',
            'role_id' => 'required',
        ], [
            'password.regex' => 'Password is not strong enough',
            'password.min' => 'Password minimum 8 characters',
            'name.required' => 'Name cannot be left blank',
            'email.required' => 'Email cannot be left blank',
            'role_id.required' => 'Role cannot be left blank',
            'user_name.required' => 'User name cannot be left blank',
        ]);
        if ($validator->fails()) {
            $loi = "";
            foreach ($validator->errors()->all() as $it) {
                $loi = $loi . '' . $it . ", ";
            };
            return response()->json([
                'code' => 400,
                'message' => $loi,
                'data' => [
                    $validator->errors()->all(),
                ],
            ], 400);
        }
        $checkEmail = User::where('email', $data['email'])->first();
        $checkUserName = User::where('user_name', $data['user_name'])->first();
        if ($checkEmail) {
            return response(['message' => 'Email already exist!'], 401);
        }
        if ($checkUserName) {
            return response(['message' => 'User name already exist!'], 401);
        }
        if ($data['password'] !==  $confirmPassword) {
            return response(['message' => 'Password two times entered does not match'], 402);
        }
        $data['password'] = Hash::make($data['password']);
        try {
            User::create($data);
            return response(['message' => 'Success'], 200);
        } catch (Exception $e) {
            return response(['message' => 'Can not to create user'], 500);
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
        $data = $request->only('email', 'name', 'user_name', 'role_id', 'password', 'url_image', 'password', 'id', 'company_name');
        $confirmPassword = $request->confirmPassword;
        $validator = Validator::make($data, [
            'id' => 'required',
            'name' => 'required',
            'email' => 'required',
            'user_name' => 'required',
            'role_id' => 'required',
        ], [
            'name.required' => 'Tên không thể bỏ trống',
            'email.required' => 'Email không thể bỏ trống',
            'username.required' => 'Tên đăng nhập không thể bỏ trống',
            'role_id.required' => 'Quyền không thể bỏ trống',
        ]);
        if (isset($data['password'])) {
            $validator = Validator::make($data, [
                'id' => 'required',
                'name' => 'required',
                'user_name' => 'required',
                'email' => 'required|email',
                'role_id' => 'required',
                'password' => 'min:8|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-]).{6,}$/',
            ], [
                'password.regex' => 'Mật khẩu không đủ mạnh',
                'password.min' => 'Mật khẩu tối thiểu 8 ký tự',
                'name.required' => 'Tên không thể bỏ trống',
                'email.required' => 'Email không thể bỏ trống',
                'user_name.required' => 'Tên đăng nhập không thể bỏ trống',
                'role_id.required' => 'Quyền không thể bỏ trống',
            ]);
        }
        if ($validator->fails()) {
            $loi = "";
            foreach ($validator->errors()->all() as $it) {
                $loi = $loi . '' . $it . ", ";
            };
            return response()->json([
                'code' => 400,
                'message' => $loi,
                'data' => [
                    $validator->errors()->all(),
                ],
            ], 400);
        }
        $checkEmail = User::where('email', $data['email'])->where('id', '<>', $data['id'])->first();
        $checkUserName = User::where('user_name', $data['user_name'])->where('id', '<>', $data['id'])->first();
        if ($checkEmail) {
            return response(['message' => 'メールアドレスは既に存在しています。 !'], 401);
        }
        if ($checkUserName) {
            return response(['message' => 'ログイン用のユーザ名は既に存在しています。 !'], 401);
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
            return response(['message' => '更新は失敗しました。']);
        }
    }
    public function updateMyUser(Request $request)
    {
        $data = $request->only('email', 'name', 'user_name', 'company_name');
        $validator = Validator::make($data, [
            'name' => 'required',
            'email' => 'required',
            'user_name' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => __('入力した内容に不備があります。入力項目を確認してください。'),
                'data' => [
                    $validator->errors()->all()
                ]
            ], 400);
        }
        $user = Auth::user();
        $checkEmail = User::where('email', $data['email'])->where('id', '<>', $user->id)->first();
        $checkUserName = User::where('user_name', $data['user_name'])->where('id', '<>', $user->id)->first();
        if ($checkEmail) {
            return response(['message' => 'メールアドレスは既に存在しています。 !'], 401);
        }
        if ($checkUserName) {
            return response(['message' => 'ログイン用のユーザ名は既に存在しています。 !'], 401);
        }
        try {
            User::find($user->id)->update($data);
        } catch (\Exception $e) {
            return response(['message' => '更新は失敗しました。']);
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
                'message' => __('入力した内容に不備があります。入力項目を確認してください。'),
                'data' => [
                    $validator->errors()->all()
                ]
            ], 400);
        }
        try {
            User::find($data['userId'])->update(['active' => $data['active']]);
            return response(['message' => 'Success'], 200);
        } catch (\Exception $e) {
            return response(['message' => '更新は失敗しました。'], 500);
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
                    $name = rand(100000, 9999999) . time() . '.' . $image->getClientOriginalExtension();
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
                    $name = rand(100000, 9999999) . time() . '.' . $image->getClientOriginalExtension();
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
                } catch (\Exception $e) {
                }
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
                'message' => '入力した内容に不備があります。入力項目を確認してください。',
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
