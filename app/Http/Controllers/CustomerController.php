<?php

namespace App\Http\Controllers;

use App\models\Comment;
use App\models\Customer;
use App\models\Lookup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{

    public function getCategories(Request $request)
    {
        $group = $request->get('group');
        if (!$group) {
            return [];
        }
        return Lookup::select('id', 'name', 'code')->where('group', $group)->get();
    }
    public function addCustomer(Request $request)
    {
        $data = $request->only(
            'company_name',
            'industry_id',
            'postal_code',
            'representative_name',
            'address',
            'phone_number',
            'homepage_url',
            'signed',
            'manager_name',
            'manager_email',
            'manager_phone',
            'person_in_charge_name',
            'person_in_charge_email',
            'person_in_charge_phone',
            'note'
        );
        $comment =  $request->only('comment');
        $userLogin = $request->only('user_name', 'email', 'name', 'url_image');
        $validator =  Validator::make($data, [
            'company_name' => 'required',
            'postal_code' => 'required',
            'address' => 'required'
        ]);
        $validatorUser =  Validator::make($userLogin, [
            'user_name' => 'required',
            'email' => 'required',
        ]);
        if ($validator->fails() || $validatorUser->fails()) {
            return response()->json([
                'message' => __('Dữ liệu không hợp lệ'),
                'data' => [
                    $validatorUser->errors()->all(),
                    $validator->errors()->all()
                ]
            ], 400);
        }
        $checkEmail = User::where('email', $userLogin['email'])->first();
        $checkUserName = User::where('user_name', $userLogin['user_name'])->first();
        if ($checkEmail) {
            return response(['message' => 'Email đã tồn tại !'], 401);
        }
        if ($checkUserName) {
            return response(['message' => 'Tên đăng nhập (User name) đã tồn tại !'], 401);
        }
        try {
            DB::beginTransaction();
            $userLogin['password'] = Hash::make(12345678);
            $userLogin['role_id'] = 2;
            $userLogin['name'] = $data['company_name'];
            $userLogin['company_name'] = $data['company_name'];
            $user = User::create($userLogin);
            $data['user_id'] = $user->id;
            Customer::create($data);
            if ($comment && $comment['comment']) {
                Comment::create([
                    'from_user_id' => Auth::user()->id,
                    'to_user_id' => $user->id,
                    'content' => $comment['comment']
                ]);
            }
            DB::commit();
            return response(['message' => 'Success'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response(['message' => 'Không thể thêm mới'], 500);
        }
    }
    public function getCustomerInfo(Request $request)
    {
        $customer_id = $request->get('customer_id', null);
        if ($customer_id == null) {
            return null;
        }
        $customer = Customer::where('id', $customer_id)->with('user:id,email,user_name')->first();
        return $customer;
    }

    public function updateCustomer(Request $request)
    {
        $data = $request->only(
            'id',
            'company_name',
            'industry_id',
            'postal_code',
            'representative_name',
            'address',
            'phone_number',
            'homepage_url',
            'signed',
            'manager_name',
            'manager_email',
            'manager_phone',
            'person_in_charge_name',
            'person_in_charge_email',
            'person_in_charge_phone',
            'note'
        );
        $comment =  $request->only('comment');
        $userLogin = $request->only('user_name', 'email', 'name', 'url_image', 'user_id');
        $validator =  Validator::make($data, [
            'id' => 'required',
            'company_name' => 'required',
            'postal_code' => 'required',
            'address' => 'required'
        ]);
        $validatorUser =  Validator::make($userLogin, [
            'user_name' => 'required',
            'email' => 'required',
            'user_id' => 'required',
        ]);
        if ($validator->fails() || $validatorUser->fails()) {
            return response()->json([
                'message' => __('Dữ liệu không hợp lệ'),
                'data' => [
                    $validatorUser->errors()->all(),
                    $validator->errors()->all()
                ]
            ], 400);
        }
        $checkEmail = User::where('email', $userLogin['email'])->where('id', '<>', $userLogin['user_id'])->first();
        $checkUserName = User::where('user_name', $userLogin['user_name'])->where('id', '<>', $userLogin['user_id'])->first();
        if ($checkEmail) {
            return response(['message' => 'Email đã tồn tại !'], 401);
        }
        if ($checkUserName) {
            return response(['message' => 'Tên đăng nhập (User name) đã tồn tại !'], 401);
        }
        try {
            DB::beginTransaction();
            $userLogin['name'] = $data['company_name'];
            $userLogin['company_name'] = $data['company_name'];
            $user = User::find($userLogin['user_id'])->update(['email' => $userLogin['email'], 'user_name' => $userLogin['user_name']]);
            Customer::find($data['id'])->update($data);
            if ($comment && $comment['comment']) {
                Comment::create([
                    'from_user_id' => Auth::user()->id,
                    'to_user_id' => $user->id,
                    'content' => $comment['comment']
                ]);
            }
            DB::commit();
            return response(['message' => 'Success'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response(['message' => 'Không thể thêm mới'], 500);
        }
    }

    public function addComment(Request $request)
    {
        $user_id = $request->get('user_id', null);
        $content = $request->get('content', null);
        if ($user_id == null || !$content) {
            return response(['message' => 'Error'], 404);
        }
        try {
            Comment::create([
                'from_user_id' => Auth::user()->id,
                'to_user_id' => $user_id,
                'content' => $content
            ]);
        } catch (\Exception $e) {
            return response(['message' => 'Không thể comment'], 500);
        }
    }

    public function getComment(Request $request)
    {
        $user_id = $request->get('user_id', null);
        if ($user_id == null) {
            return response(['message' => 'Error'], 404);
        }
        try {
            return  Comment::with('userComment')->where('to_user_id', $user_id)->orderBy('created_at', "DESC")->get();
        } catch (\Exception $e) {
            return $e;
            return response(['message' => 'Không thể comment'], 500);
        }
    }
    public function getCutomers(Request $request)
    {
        $page = $request->get('page', 1);
        $per_pager = $request->get('perPage', 5);
        $search = $request->get('search', null);
        $query = Customer::with('industry', 'user');
        if ($search != null) {
            $search = trim($search);
            $query->where('address', 'ilike', "%{$search}%")
                ->orWhere('company_name', 'ilike', "%{$search}%")
                ->orWhere('representative_name', 'ilike', "%{$search}%")
                ->orWhere('phone_number', 'ilike', "%{$search}%");
        }

        $data = $query->orderBy('updated_at', 'DESC')->paginate($per_pager, ['*'], 'page', $page);
        return $data;
    }

    public function editComment(Request $request)
    {
        $data = $request->only(
            'id',
            'comment'
        );
        $validator =  Validator::make($data, [
            'id' => 'required',
            'comment' => 'required',
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
            $comment = Comment::where('id', $data['id'])->first();
            if ($comment->from_user_id !== Auth::user()->id) {
                return response(['message' => 'Không thể xóa comment của người khác'], 422);
            }
            Comment::find($data['id'])->update([
                'content' => $data['comment']
            ]);
        } catch (\Exception $e) {
            return response(['message' => 'Không thể comment'], 500);
        }
    }

    public function importData(Request $request)
    {
        $customers = $request->get('data', []);
        try {
            DB::beginTransaction();
            foreach ($customers as $data) {
                $userLogin['password'] = Hash::make(12345678);
                $userLogin['role_id'] = 2;
                $userLogin['name'] = $data['company_name'];
                $userLogin['company_name'] = $data['company_name'];
                $userLogin['email'] = $data['manager_email'];
                $userLogin['user_name'] = $data['manager_email'];
                $user = User::create($userLogin);
                Customer::create([
                    'industry_id' => $data['industry_id'],
                    'company_name' => $data['company_name'],
                    'homepage_url' => $data['homepage_url'],
                    'manager_email' => $data['manager_email'],
                    'representative_name' => $data['representative_name'],
                    'user_id' => $user->id,
                    'address' => $data['address'],
                    'phone_number' => $data['phone_number']
                ]);
            }
            DB::commit();
            return response(['message' => 'Success'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response(['message' => 'Không thể import'], 500);
        }
    }
    public function setStatusSinged(Request $request)
    {
        $data = $request->only(
            'ids',
            'status'
        );
        $validator =  Validator::make($data, [
            'ids' => 'required',
            'status' => 'required',
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
            DB::beginTransaction();
            foreach ($data['ids'] as $id) {
                Customer::find($id)->update(['signed' => $data['status']]);
            }
            DB::commit();
            return response(['message' => 'Done']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response(['message' => 'Error'], 500);
        }
    }
}
