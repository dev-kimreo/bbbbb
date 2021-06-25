<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QpickMailSender extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $template;
    public $user;
    public $data;

    /**
     * Create a new message instance.
     *
     * @param string $code
     * @param $user
     * @param $data
     */
    public function __construct(string $code, $user, $data)
    {
        $this->user = $user;
        $this->template = EmailTemplate::where([
            'code' => $code,
            'enable' => 1
        ])->first();

        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): QpickMailSender
    {
        return $this->view('emails.' . $this->template->code)
            ->subject('[' . getenv('APP_NAME') . '] ' . $this->template->title);
    }

    public function send($mailer) {

        // 광고 수신 동의 여부 체크
        if (!$this->template->ignore_agree && is_null($this->user->advAgree) || ($this->user->advAgree && !$this->user->advAgree->agree)) {
            return;
        }

        if (is_null($this->template)) {
            return;
        }

        parent::send($mailer);
    }
}
