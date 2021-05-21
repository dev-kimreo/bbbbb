<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use Carbon\Carbon;
use Hash;

use App\Models\User;
use App\Models\SignedCode;

use App\Events\Member\VerifyEmail;
use App\Events\Member\VerifyEmailCheck;

use App\Http\Requests\Members\IndexRequest;
use App\Http\Requests\Members\ShowRequest;
use App\Http\Requests\Members\StoreRequest;
use App\Http\Requests\Members\UpdateRequest;


use App\Http\Requests\Members\CheckPwdMemberRequest;
use App\Http\Requests\Members\ModifyMemberPwdRequest;
use App\Http\Requests\Members\PasswordResetSendLinkRequest;
use App\Http\Requests\Members\CheckChangePwdAuthRequest;
use App\Http\Requests\Members\PasswordResetRequest;

use App\Exceptions\QpickHttpException;

use Config;
use URL;
use DB;
use RedisManager;
use Cache;
use Password;

use App\Jobs\SendMail;

use App\Libraries\CollectionLibrary;


class MemberController extends Controller
{
    private $user, $signedCode;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct(User $user, SignedCode $signedCode)
    {
        $this->user = $user;
        $this->signedCode = $signedCode;
    }

    public function index(IndexRequest $request)
    {

    }


    public function show(ShowRequest $request)
    {

    }

    /**
     * @OA\Post(
     *      path="/v1/user",
     *      summary="회원가입",
     *      description="회원가입",
     *      operationId="userSignIn",
     *      tags={"회원관련"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"name","email", "password", "passwordConfirmation"},
     *              @OA\Property(property="name", type="string", minimum="2", maximum="100", example="홍길동", description="이름"),
     *              @OA\Property(property="email", type="string", format="email", maximum="100", example="abcd@davinci.com", description="이메일"),
     *              @OA\Property(property="password", type="string", format="password", minimum="8", example="1234qwer", description="비밀번호"),
     *              @OA\Property(property="passwordConfirmation", type="string", format="password", minimum="8", example="1234qwer", description="비밀번호 재확인"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="created",
     *          @OA\JsonContent(ref="#/components/schemas/User")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      )
     *  )
     */

    /**
     * 회원가입
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreRequest $request)
    {
        // 비밀번호 체크
        $checkPwdRes = $this->checkPasswordPattern($request->password, $request->email);

        $this->user = $this->user::create(array_merge(
            $request->all(),
            ['password' => hash::make($request->password)]
        ));
        $this->user->refresh();

        VerifyEmail::dispatch($this->user);

        return response()->json(CollectionLibrary::toCamelCase(collect($this->user)), 201);
    }


    /**
     * @OA\Patch(
     *      path="/v1/user",
     *      summary="회원정보 수정",
     *      description="회원 정보 수정",
     *      operationId="userInfoModify",
     *      tags={"회원관련"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"name", "password"},
     *              @OA\Property(property="name", type="string", example="홍길동", description="변경할 이름"),
     *              @OA\Property(property="password", type="string", format="password", example="1234qwer", description="비밀번호"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="modified"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     */

    /**
     * 회원 정보 수정
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateRequest $request)
    {
        if (!$this::funcCheckPassword($request->password)) {
            throw new QpickHttpException(422, 'user.password.incorrect');
        }

        $member = auth()->user();
        $member->name = $request->name;
        $member->save();

        return response()->json(CollectionLibrary::toCamelCase(collect($member)), 201);
    }



    /**
     * @OA\Post(
     *      path="/v1/user/email-verification",
     *      summary="이메일 인증 재발송",
     *      description="회원 이메일 인증 재발송",
     *      operationId="userVerifyEmailReSend",
     *      tags={"회원관련"},
     *      @OA\Response(
     *          response=204,
     *          description="successfully"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     */
    /**
     * 회원 인증 메일 재발송
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendVerificationEmail(Request $request)
    {

        // 짧은 시간내에 잦은 요청으로 인해 재발송 불가 합니다.
        if (!VerifyEmailCheck::dispatch(auth()->user())) {
            throw new QpickHttpException(422, 'email.too_many_send');
        }

        // // 이미 인증된 회원입니다.
        if (!VerifyEmail::dispatch(auth()->user())) {
            throw new QpickHttpException(422, 'email.already_verified');
        }

        return response()->noContent();

    }

    /**
     * @OA\Get(
     *      path="/v1/user/email-verification/user.regist/{id}?expires={expires}&hash={hash}&signature={signature}",
     *      summary="이메일 인증",
     *      description="회원 이메일 인증",
     *      operationId="userVerifyEmail",
     *      tags={"회원관련"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="send verify email"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *  )
     */
    /**
     * 회원 메일 인증
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verification(Request $request)
    {
        $exp = explode('/', $request->path());
        $signCode = SignedCode::getBySignCode($request->id, $request->hash, $request->signature)->select('id')->first();

        // 가상 서명키 유효성 체크
        if ($request->hasValidSignature() && $signCode && $signCode['id']) {

            $member = $this->user::find($request->id);

            // 인증되지 않은 경우
            if (is_null($member->email_verified_at)) {
                $member->email_verified_at = carbon::now();
                $member->save();
            } else {
                // 이미 인증된 회원입니다.
                throw new QpickHttpException(422, 'email.already_verified');
            }

            // 가상 서명키 제거
            $signCode->delete();
        } else {
            throw new QpickHttpException(422, 'email.incorrect');
        }

        return response()->json(CollectionLibrary::toCamelCase(collect($member)));
    }


    /**
     * @OA\Post(
     *      path="/v1/user/password",
     *      summary="비밀번호 검증",
     *      description="회원 비밀번호 검증",
     *      operationId="userPasswordVerify",
     *      tags={"회원관련"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"password"},
     *              @OA\Property(property="password", type="string", format="password", example="1234qwer", description="비밀번호"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=204,
     *          description="successfully"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     */
    /**
     * 비밀번호 검증
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkPassword(CheckPwdMemberRequest $request)
    {
        if (!$this::funcCheckPassword($request->password)) {
            throw new QpickHttpException(422, 'user.password.incorrect');
        }

        return response()->noContent();
    }



    /**
     * @OA\Patch(
     *      path="/v1/user/password",
     *      summary="비밀번호 변경",
     *      description="회원 비밀번호 변경",
     *      operationId="userPwdModify",
     *      tags={"회원관련"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"password", "changePassword", "passwordConfirmation"},
     *              @OA\Property(property="password", type="string", format="password", example="1234qwer", description="기존 비밀번호"),
     *              @OA\Property(property="changePassword", type="string", format="password", example="1234qwer11", description="변경할 비밀번호"),
     *              @OA\Property(property="passwordConfirmation", type="string", format="password", example="1234qwer11", description="변경할 비밀번호 확인"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=204,
     *          description="modified"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     */

    /**
     * 회원 비밀번호 변경
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function modifyPassword(ModifyMemberPwdRequest $request)
    {
        // 현재 패스워드 체크
        if (!$this::funcCheckPassword($request->password)) {
            throw new QpickHttpException(422, 'user.password.incorrect');
        }

        // 기존 비밀번호와 변경할 비밀번호가 같을 경우
        if (hash::check($request->changePassword, auth()->user()->password)) {
            throw new QpickHttpException(422, 'user.password.reuse');
        }

        // 비밀번호 체크
        $this->checkPasswordPattern($request->changePassword, auth()->user()->email);

        $member = auth()->user();
        $member->password = hash::make($request->changePassword);
        $member->save();

        return response()->noContent();
    }




    /**
     * @OA\Post(
     *      path="/v1/user/password/reset-mail",
     *      summary="비밀번호 찾기",
     *      description="회원 비밀번호 찾기 - 변경을 위한 링크 발송",
     *      operationId="userPasswordResetSendLink",
     *      tags={"비밀번호 찾기"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"email"},
     *              @OA\Property(property="email", type="string", format="email", example="abcd@abcd.com", description="이메일"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=204,
     *          description="successfully"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      )
     *  )
     */
    /**
     * 회원 비밀번호 찾기 - 변경을 위한 메일 발송
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function passwordResetSendLink(PasswordResetSendLinkRequest $request)
    {
        // 회원 정보
        $member = $this->user::where('email', $request->email)->first();

        $verifyToken = Password::createToken($member);
        $verifyUrl = config('services.qpick.domain') . config('services.qpick.verifyPasswordPath') . '?token=' . $verifyToken . "&email=" . $request->email;

        $member = $member->toArray();

        $data = array(
            'user' => $member,
            'mail' => [
                'view' => 'emails.member.verifyPassword',
                'subject' => '비밀번호 인증 메일입니다.',
                'data' => [
                    'name' => $member['name'],
                    'url' => $verifyUrl
                ]
            ]
        );

        // 메일 발송
        SendMail::dispatch($data);

        return response()->noContent();
    }



    /**
     * @OA\Get(
     *      path="/v1/user/password/reset-mail",
     *      summary="비밀번호 찾기 Token 인증",
     *      description="비밀번호 찾기 - 변경을 위한 링크의 값 유효성 체크",
     *      operationId="userPasswordResetLinkAuth",
     *      tags={"비밀번호 찾기"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"email", "token"},
     *              @OA\Property(property="email", type="string", format="email", example="abcd@abcd.com", description="이메일"),
     *              @OA\Property(property="token", type="string", example="0731d55c489684a8245eedd046878240527c69a2a775e6164820033dd0d62e1f", description="Token"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=204,
     *          description="successfully"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      )
     * )
     */
    /**
     * 회원 비밀번호 변경 링크 검증 체크 및 변경
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePwdVerification(CheckChangePwdAuthRequest $request)
    {
        // 비밀번호 재설정 Token 발행여부 체크
        $res = DB::table('password_resets')->where('email', $request->email)->first();
        if (!$res) {
            // 일치하는 정보가 없습니다.
            throw new QpickHttpException(404, 'common.not_found');
        }

        // 회원정보
        $member = $this->user::where('email', $request->email)->first();

        // Token 유효성 체크
        if (!Password::tokenExists($member, $request->token)) {
            throw new QpickHttpException(422, 'auth.incorrect_timeout');
        }

        return response()->noContent();
    }


    /**
     * @OA\Patch(
     *      path="/v1/user/password/reset-mail",
     *      summary="비밀번호 찾기 - 변경",
     *      description="비밀번호 찾기를 통한 변경 url을 통한 후 비밀번호 변경",
     *      operationId="userPasswordReset",
     *      tags={"비밀번호 찾기"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"email", "token", "password", "passwordConfirmation"},
     *              @OA\Property(property="email", type="string", format="email", example="abcd@abcd.com", description="이메일"),
     *              @OA\Property(property="token", type="string", example="0731d55c489684a8245eedd046878240527c69a2a775e6164820033dd0d62e1f", description="Token"),
     *              @OA\Property(property="password", type="string", example="abcd1234!@", description="새로운 비밀번호"),
     *              @OA\Property(property="passwordConfirmation", type="string", example="abcd1234!@", description="새로운 비밀번호 확인"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=204,
     *          description="modified"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="not found"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      )
     * )
     */
    /**
     * 회원 비밀번호 변경 링크 검증 체크 및 변경
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function passwordReset(PasswordResetRequest $request)
    {
        // 비밀번호 재설정 Token 발행여부 체크
        $res = DB::table('password_resets')->where('email', $request->email)->first();
        if (!$res) {
            throw new QpickHttpException(404, 'common.not_found');
        }

        // 회원정보
        $member = $this->user::where('email', $request->email)->first();

        // Token 유효성 체크
        if (!Password::tokenExists($member, $request->token)) {
            throw new QpickHttpException(422, 'auth.incorrect_timeout');
        }

        // 비밀번호 체크
        $this->checkPasswordPattern($request->password, $request->email);

        // 비밀번호 변경
        $member->password = hash::make($request->password);
        $member->save();

        // 비밀번호 변경 Token 삭제
        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->noContent();
    }


    /**
     * 비밀번호 패턴 체크 함수
     *
     * @return
     */
    static function checkPasswordPattern(string $pwd, string $email = null)
    {
        /**
         * 비밀번호 패턴 체크
         */
        $chkPasswordRes = checkPwdPattern($pwd);
        if (!$chkPasswordRes['combination']) {  // 특수문자, 문자, 숫자 포함 체크
            throw new QpickHttpException(422, 'user.password.validation.characters');
        } else if (!$chkPasswordRes['continue']) {  // 연속된 문자, 동일한 문자 연속 체크
            throw new QpickHttpException(422, 'user.password.validation.repetition');
        } else if (!$chkPasswordRes['empty']) { // 공백 문자 체크
            throw new QpickHttpException(422, 'user.password.validation.used_space');
        }

        /**
         * 비밀번호와 아이디 동일 여부 체크
         */
        if (isset($email)) {
            $chkPwdSameIdRes = checkPwdSameId($pwd, $email);
            if (!$chkPwdSameIdRes) {
                throw new QpickHttpException(422, 'user.password.validation.matched_email');
            }
        }

        return true;
    }

    static function funcCheckPassword($pwd)
    {
        if (!hash::check($pwd, auth()->user()->password)) {
            return false;
        }

        return true;
    }


    public function test(Request $request)
    {
        echo gethostname() . "\r\n";

        $aaa = gethostname();

        $bbb = 'asdasasd';

        $c = rand(1, 100);

        echo 'asdad';
    }

    public function testa()
    {
        echo gethostname();
        print_r(Auth::user());
    }


}
