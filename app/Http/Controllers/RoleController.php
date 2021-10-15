<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\RoleMenu;
use App\Models\SystemMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function getRoles()
    {
        return Role::get();
    }

    public function getMenuRole(Request $request)
    {
        $roleId = $request->get('roleId', null);
        if ($roleId) {
            $menuIds = RoleMenu::where('role_id', $roleId)->pluck('menu_id')->toArray();
            $menus = SystemMenu::where('parent_id', null)->with('children')->get();
            foreach ($menus as $item) {
                if (in_array($item['id'], $menuIds)) {
                    $item['role'] = true;
                    $children = $item->children;
                    foreach ($children as $child) {
                        if (in_array($child['id'], $menuIds)) {
                            $child['role'] = true;
                        } else {
                            $child['role'] = false;
                        }
                    }
                } else {
                    $item['role'] = false;
                }
            }
            return $menus;
        }
        return [];
    }

    public function updateRoleMenu(Request $request)
    {
        $data = $request->all();
        $validator =  Validator::make($data, [
            'roleId' => 'required',
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
            RoleMenu::where('role_id', $data['roleId'])->delete();
            if (isset($data['menu']) && count($data['menu'])) {
                foreach ($data['menu'] as $menu) {
                    RoleMenu::create([
                        'role_id' => $data['roleId'],
                        'menu_id' => $menu
                    ]);
                }
            }
            DB::commit();
            return response(['message' => 'Success'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response(['message' => '更新は失敗しました。'], 501);
        }
    }
}
