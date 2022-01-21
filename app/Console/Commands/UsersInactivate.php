<?php

namespace App\Console\Commands;

use App\Models\Users\User;
use App\Models\Users\UserPrivacyInactive;
use App\Services\UserService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class UsersInactivate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:inactivate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '활성사용자를 비활성(휴면)사용자로 전환';

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

        User::query()
            ->whereNull('inactivated_at')
            ->whereNotNull('last_authorized_at')
            ->where('last_authorized_at', '<', $baseDate->subDays(config('custom.user.toInactiveDays')))
            ->get()
            ->each(function ($user) {
                UserService::inactivate($user);
            });
    }
}
