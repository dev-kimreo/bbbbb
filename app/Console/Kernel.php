<?php

namespace App\Console;

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

        // User 관련
        // TODO 주기 정해지면 적용해서 살려야함
//        $schedule->command('switch:userInactive')->daily();   // 활성화 회원 -> 휴먼회원으로 전환
//        $schedule->command('delete:privacyDeletedUser')->daily();   // 탈퇴 회원 개인정보 영구 삭제

        // Telescope 데이터 제거
        $schedule->command('telescope:prune')->daily();

        // 다국어 리소스 캐시
        $schedule->command('build:translations')->everyFiveMinutes();
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
