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



class MemberEventSubscriber
{

    public $subName = 'verification.verify';

    public function handleMemberVerifyEmail($event) {

        // 이메일 인증되지 않은 회원일 경우
        if ($event->user instanceof MustVerifyEmail && !$event->user->hasVerifiedEmail() ) {

            // 고유 url 생성
            $verifyBeUrl = URL::temporarySignedRoute(
                $this->subName,
                Carbon::now()->addMinutes(Config::get('auth.verification.expire', 30)),
                [
                    'id' => $event->user->no,
                    'hash' => sha1($event->user->email)
                ]
            );

            $urlArr = parse_url($verifyBeUrl);
            preg_match('/^.*\/([0-9]+)$/', $urlArr['path'], $idMatch);
            $urlArr['query'] = 'id=' . $idMatch[1] . '&' . $urlArr['query'];

            $verifyUrl = config('services.davinci.domain') . config('services.davinci.verifyPath') . '?' . $urlArr['query'];

            preg_match('/signature=(.*)/', $verifyBeUrl, $match);

            $user = array(
                'email' => $event->user->email,
                'name' => $event->user->name
            );

            $data = array(
                'url' => $verifyUrl,
                'name' => $user['name']
            );

            Mail::send('emails.welcome', $data, function($message) use ($user){
                $message->from(config('mail.from.address'), config('mail.from.name'));
                $message->to($user['email'], $user['name'])->subject('다빈치 인증 메일입니다.');
            });

            // 고유 생성 sign 키 저장
            $signedModel = New SignedCodes;
            $signedModel->name = $this->subName;
            $signedModel->name_key = $event->user->no;
            $signedModel->hash = sha1($event->user->email);
            $signedModel->sign = $match[1];
            $signedModel->save();

            return true;
        } else {
            return false;
        }
    }

    public function handleMemberVerifyEmailCheck($event) {

        $signCount = SignedCodes::where('name',  $this->subName)
                                ->where('name_key', $event->user->no)
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
            queueable(
            'App\Events\Member\VerifyEmail',
            [MemberEventSubscriber::class, 'handleMemberVerifyEmail']
            )
        );

        $events->listen(
            'App\Events\Member\VerifyEmailCheck',
            [MemberEventSubscriber::class, 'handleMemberVerifyEmailCheck']
        );


    }
}
