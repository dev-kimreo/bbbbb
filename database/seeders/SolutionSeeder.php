<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SolutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('solutions')->insert([
            [
                'name' => '공통',
                'created_at' => Carbon::now()
            ],
            [
                'name' => '메이크샵',
                'created_at' => Carbon::now()
            ],
            [
                'name' => '마이소호',
                'created_at' => Carbon::now()
            ]
        ]);
    }
}
