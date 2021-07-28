<?php

namespace Database\Seeders;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ActionLogTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ini_set('memory_limit', '2048M');

        $crud = ['c', 'r', 'u', 'd'];
        $types = array_keys(Relation::morphMap());
        $date = Carbon::now();

        for ($i=0; $i<=500000; $i++) {
            $date = $date->addSeconds(rand(5,40));
            shuffle($crud);
            shuffle($types);

            if (rand(0,1000) < 50) {
                DB::table('action_logs')->insert(
                    [
                        [
                            'conn_id' => Str::random(32),
                            'client_id' => rand(1, 2),
                            'user_id' => rand(1, 3),
                            'user_grade' => 1,
                            'ip' => rand(100, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255),
                            'loggable_type' => 'user',
                            'loggable_id' => rand(1, 3),
                            'crud' => 'r',
                            'path' => 'v1/user/auth',
                            'title' => '로그인',
                            'memo' => '로그인',
                            'properties' => '{"user_grade":1,"manager_id":null,"changes":[]}',
                            'created_at' => $date
                        ]
                    ]
                );
            } elseif (rand(0,1000) < 10) {
                $admin_id = rand(1, 2);
                DB::table('action_logs')->insert(
                    [
                        [
                            'conn_id' => Str::random(32),
                            'client_id' => 1,
                            'user_id' => $admin_id,
                            'user_grade' => 1,
                            'ip' => rand(100, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255),
                            'loggable_type' => 'user',
                            'loggable_id' => 3,
                            'crud' => 'r',
                            'path' => 'v1/user/3/auth',
                            'title' => '로그인',
                            'memo' => '관리자 로그인',
                            'properties' => '{"user_grade":1,"manager_id":' . $admin_id . ',"changes":[]}',
                            'created_at' => $date
                        ]
                    ]
                );
            } else {
                DB::table('action_logs')->insert(
                    [
                        [
                            'conn_id' => Str::random(32),
                            'client_id' => rand(1, 2),
                            'user_id' => rand(1, 3),
                            'user_grade' => 1,
                            'ip' => rand(100, 255) . '.' . rand(0, 255) . '.' . rand(0, 255) . '.' . rand(0, 255),
                            'loggable_type' => $types[0],
                            'loggable_id' => rand(1, 3),
                            'crud' => $crud[0],
                            'path' => 'v1/test',
                            'title' => Str::random(32),
                            'memo' => Str::random(128),
                            'properties' => '[]',
                            'created_at' => $date
                        ]
                    ]
                );
            }
        }
    }
}
