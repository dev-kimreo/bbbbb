<?php

namespace App\Console\Commands;

use App\Models\Users\User;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UsersAutoWithdrawal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:autoWithdrawal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
            ->whereNotNull('inactivated_at')
            ->where('inactivated_at', '<', $baseDate->subDays(config('custom.user.toAutoWithdrawalDays')))
            ->get()
            ->each(function ($user) {
                UserService::withdrawal($user);
            });
    }
}
