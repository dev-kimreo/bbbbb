<?php

namespace App\Console;

use App\Models\Users\User;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        Commands\DeleteTempAttachs::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        // Telescope 데이터 제거
        $schedule->command('telescope:prune')->daily();

        // 다국어 리소스 캐시
        $schedule->command('build:translations')->everyFiveMinutes();

        // 장기 미접속 회원 휴면처리
        $schedule->command('users:inactivate')->dailyAt('16:00'); // KST 1:00, UTC 16:00

        // 장기 휴면회원 탈퇴처리
        $schedule->command('users:autoWithdrawal')->dailyAt('16:00'); // KST 1:00, UTC 16:00

        // 탈퇴회원 개인정보 파기
        $schedule->command('users:destruct')->dailyAt('16:00'); // KST 1:00, UTC 16:00

        // 휴면 및 탈퇴처리 예고 알림
        $schedule->command('users:priorNotice')->dailyAt('16:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
