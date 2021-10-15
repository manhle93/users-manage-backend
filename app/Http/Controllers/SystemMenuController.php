<?php

namespace App\Http\Controllers;

use App\Models\RoleMenu;
use Illuminate\Http\Request;
use App\Models\SystemMenu;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\MenuResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SystemMenuController extends Controller
{
    public function getRoleMenu()
    {
        $user = Auth::user();
        if (!$user) {
            return response(['message' => 'Vui lòng đăng nhập'], 403);
        }
        $menuIds = RoleMenu::where('role_id', $user->role_id)->pluck('menu_id')->toArray();
        $query = SystemMenu::whereIn('id', $menuIds)->with('children:id,name,parent_id,icon,hidden,order');
        $data =  $query->orderBy('order', 'ASC')->get();
        return MenuResource::collection($data);
    }

    public function getMenuTable(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = $request->get('itemsPerPage', 10);
        $search = $request->get('search', null);
        $query = SystemMenu::with(['parent:id,name,order,icon', 'roles']);
        if ($search != null) {
            $search = trim($search);
            $query->where('name', 'ilike', "%{$search}%")->orWhereHas('parent', function ($query) use ($search) {
                $query->where('name', 'ilike', "%{$search}%");
            });
        }
        $data = $query->orderBy('order', 'ASC')->paginate($perPage, ['*'], 'page', $page);
        return $data;
    }

    public function getParentMenu()
    {
        return SystemMenu::where('parent_id', null)->get();
    }

    public function editMenu(Request $request)
    {
        $data = $request->only('parent_id', 'name', 'order', 'icon', 'hidden', 'id');
        $roles = $request->get('roles');
        $validator = Validator::make($data, [
            'name' => 'required',
            'id' => 'required'
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
            DB::beginTransaction();
            SystemMenu::find($data['id'])->update($data);
            RoleMenu::where('menu_id', $data['id'])->delete();
            RoleMenu::where('menu_id', $data['parent_id'])->delete();
            if ($roles && count($roles) > 0) {
                foreach ($roles as $role) {
                    RoleMenu::create([
                        'menu_id' => $data['id'],
                        'role_id' => $role
                    ]);
                    if($data['parent_id']){
                        RoleMenu::create([
                            'menu_id' =>$data['parent_id'],
                            'role_id' => $role
                        ]);
                    }
                }
            }
            DB::commit();
            return response(['message' => 'Thành công'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response(['message' => '更新は失敗しました。'], 500);
        }
    }

    public function addMenu(Request $request)
    {
        $data = $request->only('parent_id', 'name', 'order', 'icon', 'hidden');
        $roles = $request->get('roles');
        $validator = Validator::make($data, [
            'name' => 'required',
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
            DB::beginTransaction();
            $menu = SystemMenu::create($data);
            if ($roles && count($roles) > 0) {
                foreach ($roles as $role) {
                    RoleMenu::create([
                        'menu_id' => $menu->id,
                        'role_id' => $role
                    ]);
                }
            }
            DB::commit();
            return response(['message' => 'Thành công'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response(['message' => 'Không thể tạo Menu'], 500);
        }
    }

    public function xoaMenu(Request $request){
        $data = $request->all();
        $validator = Validator::make($data, [
            'id' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => __('Menu không tồn tại'),
                'data' => [
                    $validator->errors()->all()
                ]
            ], 400);
        }
        try{
            SystemMenu::find($data['id'])->delete();
        }catch(\Exception $e){
            return response(['message' => '削除は失敗しました。']);
        }
    }
}
