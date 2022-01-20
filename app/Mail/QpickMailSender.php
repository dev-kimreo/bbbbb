<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\Users\User;
use App\Services\BladeTemplateService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QpickMailSender extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected EmailTemplate $template;
    public User $user;
    public array $data;

    /**
     * Create a new message instance.
     *
     * @param string $code
     * @param User $user
     * @param array $data
     */
    public function __construct(string $code, User $user, array $data)
    {
        $user->getAttribute('privacy');
        $this->user = $user;
        $this->template = EmailTemplate::where(
            [
                'code' => $code,
                'enable' => 1
            ]
        )->first();
        $this->data = $data;

        return true;
    }

    /**
     * Build the message.
     *
     * @return $this
     * @throws Exception
     */
    public function build(): QpickMailSender
    {
        return $this
            ->html(BladeTemplateService::instance()->compileWiths($this->template->contents, [
                'user' => $this->user->toArray(),
                'data' => $this->data
            ]))
            ->subject('[' . getenv('APP_NAME') . '] ' . $this->template->title);
        /*
        return $this->view('emails.' . $this->template->code)
            ->subject('[' . getenv('APP_NAME') . '] ' . $this->template->title);
        */
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
