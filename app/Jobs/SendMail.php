<?php

namespace App\Jobs;

use App\Models\SignedCode;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;

class SendMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // 메일 발송
        Mail::send($this->data['mail']['view'], $this->data['mail']['data'], function($message) {
            $message->from(config('mail.from.address'), config('mail.from.name'));
            $message->to($this->data['user']['email'], $this->data['user']['name'])->subject('[' . getenv('APP_NAME') . '] ' . $this->data['mail']['subject']);
        });

        if ( isset($this->data['verification']) && is_array($this->data['verification']) ) {
//            // 고유 생성 sign 키 저장
            $signedModel = New SignedCode;
            $signedModel->user_id = $this->data['user']['id'];
            $signedModel->hash = sha1($this->data['user']['email']);
            $signedModel->sign = $this->data['verification']['sign'];
            $signedModel->save();
        }

    }
}
