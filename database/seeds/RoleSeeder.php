<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = 
        [
            [
                "name" => "管理者",
                "code" => "admin",
                "description" => "admin",
            ],
            [
                "name" => "一般ユーザー",
                "code" => "employee",
                "description" => "employee",
            ]
        ];
        foreach($roles as $item){
            DB::table('roles')->insert($item);
        }
    }
}
