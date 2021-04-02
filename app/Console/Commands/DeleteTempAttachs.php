<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Storage;
use Carbon\Carbon;

class DeleteTempAttachs extends Command
{

    public $expireTime = 86400; // 임시 파일 제거 1일
    public $delFiles = [];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tempAttach:delete';

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
     * @return int
     */
    public function handle()
    {
        $files = Storage::disk('temp')->files();
        $now = Carbon::now();

        foreach ( $files as $f ) {
            $lastTime = Storage::disk('temp')->lastModified($f);

            if ( $now->timestamp - $lastTime >= $this->expireTime ) {
                $this->delFiles[] = $f;
            }
        }

        Storage::disk('temp')->delete($this->delFiles);


    }
}
