<?php

namespace App\Console\Commands;

use App\Models\Users\User;
use App\Services\UserService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class UsersDestruct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:destruct';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '탈퇴한 회원의 개인정보 영구삭제';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $baseDate = Carbon::now()->setTime(15, 0, 0);

        User::onlyTrashed()
            ->where('deleted_at', '<', $baseDate->subDays(config('custom.user.toDestructDays')))
            ->get()
            ->each(function ($user) {
                UserService::destruct($user);
            });
    }
}
