<?php

namespace App\Listeners;

use App\Mail\QpickMailSender;
use App\Models\SignedCode;
use Carbon\Carbon;
use Config;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Mail;
use URL;

class MemberEventSubscriber
{
    public string $subName = 'verification.verify';
    public string $verifyKey = 'user.regist';

    public function handleMemberVerifyEmail($event): bool
    {
        // 이메일 인증되지 않은 회원일 경우
        if ($event->user instanceof MustVerifyEmail && !$event->user->hasVerifiedEmail()) {
            // 고유 url 생성
            $verifyBeUrl = URL::temporarySignedRoute(
                $this->subName,
                Carbon::now()->addMinutes(Config::get('auth.verification.expire')),
                [
                    'verifyKey' => $this->verifyKey,
                    'user_id' => $event->user->id,
                    'hash' => sha1($event->user->privacy->email)
                ]
            );

            $urlArr = parse_url($verifyBeUrl);
            preg_match('/^.*\/([0-9]+)$/', $urlArr['path'], $idMatch);
            $urlArr['query'] = 'id=' . $idMatch[1] . '&' . $urlArr['query'];

            $verifyUrl = config('services.qpick.domain') . config('services.qpick.verifyPath') . '?' . $urlArr['query'];

            preg_match('/signature=(.*)/', $verifyBeUrl, $match);

            $data = array(
                'url' => $verifyUrl
            );

            Mail::to($event->user->privacy)->send(new QpickMailSender('Users.EmailVerification', $event->user, $data));

            // 고유 생성 sign 키 저장
            $signedModel = New SignedCode;
            $signedModel->user_id = $event->user->id;
            $signedModel->hash = sha1($event->user->privacy->email);
            $signedModel->sign = $match[1];
            $signedModel->save();

            return true;
        } else {
            return false;
        }
    }

    // 메일 발송 갯수 제한 체크
    public function handleMemberVerifyEmailCheck($event): bool
    {
        $signCount = SignedCode::where('user_id', $event->user->id)
            ->where('created_at', '>', carbon::now()->subMinutes(Config::get('auth.verification.send_limit_minutes')))
            ->get()
            ->count();

        if ($signCount >= Config::get('auth.verification.send_limit_count')) {
            return false;
        }

        return true;
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  Dispatcher $events
     */
    public function subscribe(Dispatcher $events){
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
