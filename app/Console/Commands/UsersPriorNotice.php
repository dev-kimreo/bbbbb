<?php

namespace App\Console\Commands;

use App\Mail\QpickMailSender;
use App\Models\Users\User;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Mail;

class UsersPriorNotice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:priorNotice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '휴면처리 및 탈퇴처리 예고알림 메일 발송';

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
     * @return int
     */
    public function handle(): int
    {
        // 휴면처리 알림메일
        $baseDate = Carbon::now()->setTime(15, 0, 0)->subDays(config('custom.user.toInactiveDays'));
        $baseDates = [
            $baseDate->clone()->addDays(30),
            $baseDate->clone()->addDays(10)
        ];

        foreach ($baseDates as $date) {
            User::query()
                ->whereNull('inactivated_at')
                ->whereNotNull('last_authorized_at')
                ->whereBetween('last_authorized_at', [$date->clone()->subDay(), $date])
                ->get()
                ->each(function ($user) use ($date) {
                    $data = [
                        'email' => $user->privacy->email,
                        'dateInactivate' => $date->addDays(config('custom.user.toInactiveDays'))->format('Y년 n월 j일')
                    ];
                    $user->getAttribute('privacy');

                    Mail::to($user->privacy->email)
                        ->send(
                            new QpickMailSender('Users.InactivatePriorNotice', $user, $data)
                        );
                });
        }

        // 탈퇴처리 알림메일
        $baseDate = Carbon::now()->setTime(15, 0, 0)->subDays(config('custom.user.toAutoWithdrawalDays'));
        $baseDates = [
            $baseDate->clone()->addDays(30),
            $baseDate->clone()->addDays(10)
        ];

        foreach ($baseDates as $date) {
            User::query()
                ->whereNotNull('inactivated_at')
                ->whereBetween('inactivated_at', [$date->clone()->subDay(), $date])
                ->get()
                ->each(function ($user) use ($date) {
                    $user::status('inactive');

                    $data = [
                        'email' => $user->privacy->email,
                        'dateWithdrawal' => $date->addDays(config('custom.user.toAutoWithdrawalDays'))->format('Y년 n월 j일')
                    ];

                    Mail::to($user->privacy->email)
                        ->send(
                            new QpickMailSender('Users.AutoWithdrawalPriorNotice', $user, $data)
                        );
                });
        }

        return 1;
    }
}
