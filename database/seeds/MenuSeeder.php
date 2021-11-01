<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $menus =
            [
                [
                    "parent_id" => null,
                    "name" => "ダッシュボード",
                    "icon" =>  "mdi-grid-large",
                    "order" => 1
                ],
                [
                    "parent_id" => null,
                    "name" => "ユーザー",
                    "icon" => "mdi-account",
                    "order" => 2
                ],
                [
                    "parent_id" => 2,
                    "name" => "プロファイル",
                    "icon" => "mdi-information",
                    "order" => 1
                ],
                [
                    "parent_id" => 2,
                    "name" => "ユーザー管理",
                    "icon" => "mdi-account-multiple",
                    "order" => 2
                ],
                [
                    "parent_id" => 2,
                    "name" => "Menu",
                    "icon" => "mdi-menu",
                    "order" => 4
                ],
                [
                    "parent_id" => 2,
                    "name" => "権限",
                    "icon" => "mdi-wrench",
                    "order" => 3
                ],
                [
                    "parent_id" => null,
                    "name" => "顧客情報",
                    "icon" => "mdi-account-multiple",
                    "order" => 3
                ],
                [
                    "parent_id" => 7,
                    "name" => "顧客管理",
                    "icon" => "mdi-home-modern",
                    "order" => 1
                ],


            ];
        foreach ($menus as $item) {
            DB::table('system_menus')->insert([
                'name' =>$item['name'],
                'icon' =>$item['icon'],
                "parent_id" => $item["parent_id"],
                "order" => $item["order"]
            ]);
        }
    }
}
