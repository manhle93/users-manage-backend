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
                "name" => "System Admin",
                "code" => "sysadmin",
                "description" => "For Developer",
            ],
            [
                "name" => "Admin",
                "code" => "admin",
                "description" => "Quản trị viên hệ thống",
            ],
            [
                "name" => "Manager",
                "code" => "manager",
                "description" => "Quản lý",
            ],
            [
                "name" => "Employee",
                "code" => "employee",
                "description" => "Nhân viên",
            ],
        ];
        foreach($roles as $item){
            DB::table('roles')->insert($item);
        }
    }
}
