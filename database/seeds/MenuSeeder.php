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
                    "name" => "Tổng quan",
                    "icon" =>  "mdi-grid-large",
                    "order" => 1
                ],
                [
                    "parent_id" => null,
                    "name" => "Người dùng",
                    "icon" => "mdi-account",
                    "order" => 2
                ],
                [
                    "parent_id" => null,
                    "name" => "UI Elements",
                    "icon" => "mdi-material-ui",
                    "order" => 10000
                ],

                [
                    "parent_id" => 2,
                    "name" => "Thông tin",
                    "icon" => "mdi-information",
                    "order" => 1
                ],
                [
                    "parent_id" => 2,
                    "name" => "Quản lý người dùng",
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
                    "name" => "Phân quyền",
                    "icon" => "mdi-wrench",
                    "order" => 3
                ],


                [
                    "parent_id" => 6,
                    "name" => "Icons",
                    "icon" => "mdi-movie-roll",
                    "order" => 1
                ],
                [
                    "parent_id" => 6,
                    "name" => "Notifications",
                    "icon" => "mdi-bell",
                    "order" => 2
                ],
                [
                    "parent_id" => 6,
                    "name" => "Maps",
                    "icon" => "mdi-map",
                    "order" => 3
                ],
                [
                    "parent_id" => 6,
                    "name" => "Charts",
                    "icon" => "mdi-chart-areaspline",
                    "order" => 4
                ],
                [
                    "parent_id" => 6,
                    "name" => "Tables",
                    "icon" => "mdi-table-edit",
                    "order" => 5
                ],
                [
                    "parent_id" => 6,
                    "name" => "Typography",
                    "icon" => "mdi-format-color-text",
                    "order" => 6
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
