<?php

namespace App\Console\Commands;

use App\Models\Users\User;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SwitchToInactiveUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'switch:userInactive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '활성화 사용자를 비활성 사용자로 전환';

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
        $users = User::where('last_authorized_at', '<=', Carbon::now()->addDays(-1 * config('custom.user.toInactiveDays')))->get();

        $users->each(function ($item) {
            $item->inactivated_at = Carbon::now();
            $item->save();

            $activePrivacy = $item->privacy->toArray();
            $item->privacy->delete();

            $item::status('inactive');
            $item->privacy()->create(array_merge($activePrivacy, ['user_id' => $item->id]));
        });
    }
}
