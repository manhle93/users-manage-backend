<?php

use App\Models\SystemMenu;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users =
            [
                [
                    "name" => "Nguyen Tran Lich",
                    "user_name" => "admin",
                    "email" => "lichntbk@gmail.com",
                    "password" => Hash::make(12345678),
                    "role_id" =>  1,
                    "created_at"=> Carbon::now(),
                    "updated_at"=> Carbon::now()
                ],
                [
                    "name" => "Tran Van Duc",
                    "user_name" => "mavuong",
                    "email" => "mavuong20131073@gmail.com",
                    "password" => Hash::make(12345678),
                    "role_id" =>  1,
                    "created_at"=> Carbon::now(),
                    "updated_at"=> Carbon::now()
                ],
                [
                    "name" => "Nguyen Thi Thu",
                    "user_name" => "thuthu",
                    "email" => "usbusbubs@gmail.com",
                    "password" => Hash::make(12345678),
                    "role_id" =>  2,
                    "created_at"=> Carbon::now(),
                    "updated_at"=> Carbon::now()
                ],
            ];

        foreach ($users as $item) {
          $user =  DB::table('users')->insert($item);
        }
        $menu_ids = SystemMenu::pluck('id')->toArray();
        foreach ($menu_ids as $item) {
            DB::table('role_menus')->insert([
                'role_id' => 1,
                'menu_id' => $item
            ]);
        }
    }
}
