<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;
use function Illuminate\Events\queueable;

use App\Models\SignedCodes;
use App\Jobs\SendMail;



class MemberEventSubscriber
{

    public $subName = 'verification.verify';
    public $verifyKey = 'member.regist';

    public function handleMemberVerifyEmail($event) {

        // 이메일 인증되지 않은 회원일 경우
        if ($event->user instanceof MustVerifyEmail && !$event->user->hasVerifiedEmail() ) {
            // 고유 url 생성
            $verifyBeUrl = URL::temporarySignedRoute(
                $this->subName,
                Carbon::now()->addMinutes(Config::get('auth.verification.expire')),
                [
                    'verifyKey' => $this->verifyKey,
                    'id' => $event->user->no,
                    'hash' => sha1($event->user->email)
                ]
            );

            $urlArr = parse_url($verifyBeUrl);
            preg_match('/^.*\/([0-9]+)$/', $urlArr['path'], $idMatch);
            $urlArr['query'] = 'id=' . $idMatch[1] . '&' . $urlArr['query'];

            $verifyUrl = config('services.qpick.domain') . config('services.qpick.verifyPath') . '?' . $urlArr['query'];

            preg_match('/signature=(.*)/', $verifyBeUrl, $match);

            $data = array(
                'verification' => [
                    'name' => $this->verifyKey,
                    'no' => $event->user->no,
                    'email' => $event->user->email,
                    'sign' => $match[1]
                ],
                'user' => $event->user->toArray(),
                'mail' => [
                    'view' => 'emails.welcome',
                    'subject' => '인증 메일입니다.',
                    'data' => [
                        'name' => $event->user->name,
                        'url' => $verifyUrl
                    ]
                ]
            );
            SendMail::dispatch($data);

            return true;
        } else {
            return false;
        }
    }

    // 메일 발송 갯수 제한 체크
    public function handleMemberVerifyEmailCheck($event) {

        $signCount = SignedCodes::where('name',  $this->verifyKey)
                                ->where('name_id', $event->user->no)
                                ->where('created_at', '>', carbon::now()->subMinutes(Config::get('auth.verification.send_limit_minutes')))->get()->count();

        if ($signCount >= Config::get('auth.verification.send_limit_count')) {
            return false;
        }

        return true;
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events){
        $events->listen(
            'App\Events\Member\VerifyEmail',
            [MemberEventSubscriber::class, 'handleMemberVerifyEmail']
        );

        $events->listen(
            'App\Events\Member\VerifyEmailCheck',
            [MemberEventSubscriber::class, 'handleMemberVerifyEmailCheck']
        );


    }
}
