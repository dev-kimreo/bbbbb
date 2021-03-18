<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\SignedCodes;

class SendVerificationMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user, $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $data)
    {
        //
        $this->user = $user;
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $mailData = [
            'url' => $this->data['url'],
            'name' => $this->user->name,
        ];

        // 메일 발송
        Mail::send('emails.welcome', $mailData, function($message) {
            $message->from(config('mail.from.address'), config('mail.from.name'));
            $message->to($this->user->email, $this->user->name)->subject('다빈치 인증 메일입니다.');
        });

        // 고유 생성 sign 키 저장
        $signedModel = New SignedCodes;
        $signedModel->name = $this->data['subName'];
        $signedModel->name_key = $this->user->no;
        $signedModel->hash = sha1($this->user->email);
        $signedModel->sign = $this->data['sign'];
        $signedModel->save();
    }
}
