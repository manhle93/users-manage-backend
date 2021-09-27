<?php

use App\models\Lookup;
use Illuminate\Database\Seeder;

class LookupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'name' => '新規事業',
                'group' => 'industry'
            ],
            [
                'name' => '美容サロン',
                'group' => 'industry'
            ],
            [
                'name' => '美容院',
                'group' => 'industry'
            ],
            [
                'name' => 'マッサージ',
                'group' => 'industry'
            ]
        ];
        foreach($data as $item){
            Lookup::create($item);
        }
        echo 'Done';
    }
}
