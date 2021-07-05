<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;

class DeletePrivacyOfDeletedUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:privacyDeletedUser';

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
        $users = User::onlyTrashed()->where('deleted_at', '<=', Carbon::now()->addDays(-1 * config('custom.user.deleted.permanentDeleteDays')))->get();

        $users->each(function ($item) {
            $item::status('deleted');
            $item->privacy->delete();
        });
    }
}
