<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use Carbon\Carbon;
use Hash;

use App\Models\User;
use App\Models\SignedCodes;

use App\Events\Member\VerifyEmail;
use App\Events\Member\VerifyEmailCheck;

use App\Http\Requests\Members\StoreMembersRequest;
use App\Http\Requests\Members\CheckPwdMemberRequest;
use App\Http\Requests\Members\ModifyMemberRequest;
use App\Http\Requests\Members\ModifyMemberPwdRequest;
use App\Http\Requests\Members\PasswordResetSendLinkRequest;
use App\Http\Requests\Members\CheckChangePwdAuthRequest;
use App\Http\Requests\Members\PasswordResetRequest;

use Config;
use URL;
use DB;
use RedisManager;
use Cache;
use Password;

use App\Jobs\SendMail;

use App\Libraries\CollectionLibrary;


/**
 * @OA\Schema (
 *      schema="passwordPattern",
 *      @OA\Property(
 *          property="110101",
 *          type="object",
 *          description="password 는 특수문자, 알파벳, 숫자 3가지가 조합되어야 합니다.",
 *          @OA\Property(
 *              property="key",
 *              type="string",
 *              description="password",
 *              example="password",
 *          ),
 *          @OA\Property(
 *              property="message",
 *              type="string",
 *          ),
 *      ),
 *      @OA\Property(
 *          property="110102",
 *          type="object",
 *          description="password 는 연속 된 문자와 동일한 문자로 4 회 연속 사용할 수 없습니다.",
 *          @OA\Property(
 *              property="key",
 *              type="string",
 *              description="password",
 *              example="password",
 *          ),
 *          @OA\Property(
 *              property="message",
 *              type="string",
 *          ),
 *      ),
 *      @OA\Property(
 *          property="110103",
 *          type="object",
 *          description="password 는 공백문자를 포함할 수 없습니다.",
 *          @OA\Property(
 *              property="key",
 *              type="string",
 *              description="password",
 *              example="password",
 *          ),
 *          @OA\Property(
 *              property="message",
 *              type="string",
 *          ),
 *      ),
 *      @OA\Property(
 *          property="110114",
 *          type="object",
 *          description="password 는 email과 4자 이상 동일 할 수 없습니다.",
 *          @OA\Property(
 *              property="key",
 *              type="string",
 *              description="password",
 *              example="password",
 *          ),
 *          @OA\Property(
 *              property="message",
 *              type="string",
 *          ),
 *      ),
 *  )
 */
class MemberController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * @OA\Post(
     *      path="/v1/member",
     *      summary="회원가입",
     *      description="회원가입",
     *      operationId="memberSignIn",
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
     *          response=200,
     *          description="successfully registered",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example="14", description="회원번호"),
     *              @OA\Property(property="name", type="string", example="홍길동", description="이름"),
     *              @OA\Property(property="email", type="string", format="email", example="abcd@davinci.com", description="이메일"),
     *              @OA\Property(property="grade", type="integer", example="0", description="회원 등급"),
     *              @OA\Property(property="emailVerifiedAt", type="string", format="timezone", example="null", description="이메일 인증일자"),
     *              @OA\Property(property="createdAt", type="string", format="timezone", example="2021-03-10T00:25:31+00:00", description="가입일자"),
     *              @OA\Property(property="updatedAt", type="string", format="timezone",  example="2021-03-10T00:25:31+00:00", description="수정일자"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed registered",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                  @OA\Property(
     *                      property="statusCode",
     *                      type="object",
     *                      allOf={
     *                          @OA\Schema(
     *                              @OA\Property(property="100001", ref="#/components/schemas/RequestResponse/properties/100001"),
     *                              @OA\Property(property="100002", ref="#/components/schemas/RequestResponse/properties/100002"),
     *                              @OA\Property(property="100011", ref="#/components/schemas/RequestResponse/properties/100011"),
     *                              @OA\Property(property="100053", ref="#/components/schemas/RequestResponse/properties/100053"),
     *                              @OA\Property(property="100063", ref="#/components/schemas/RequestResponse/properties/100063"),
     *                              @OA\Property(property="100073", ref="#/components/schemas/RequestResponse/properties/100073"),
     *                              @OA\Property(property="100101", ref="#/components/schemas/RequestResponse/properties/100101"),
     *                          ),
     *                          @OA\Schema(ref="#/components/schemas/passwordPattern")
     *                      }
     *                  ),
     *              ),
     *          )
     *      )
     *  )
     */

    /**
     * 회원가입
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(StoreMembersRequest $request)
    {

        // 비밀번호 체크
        $checkPwdRes = $this->checkPasswordPattern($request->password, $request->email);
        if ($checkPwdRes !== true) {
            return response()->json($checkPwdRes, 422);
        }

        $member = User::create(array_merge(
            $request->all(),
            ['password' => hash::make($request->password)]
        ));

        VerifyEmail::dispatch($member);

        return response()->json([
            'message' => __('common.registered'),
            'member' => CollectionLibrary::toCamelCase(collect($member))
        ], 200);
    }


    /**
     * @OA\Get(
     *      path="/v1/member",
     *      summary="회원정보",
     *      description="회원정보",
     *      operationId="memberInfo",
     *      tags={"회원관련"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully registered",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example="14", description="회원번호"),
     *              @OA\Property(property="name", type="string", example="홍길동", description="이름"),
     *              @OA\Property(property="email", type="string", format="email", example="abcd@davinci.com", description="이메일"),
     *              @OA\Property(property="grade", type="integer", example="0", description="회원 등급"),
     *              @OA\Property(property="emailVerifiedAt", type="string", format="timezone", example="null", description="이메일 인증일자"),
     *              @OA\Property(property="createdAt", type="string", format="timezone", example="2021-03-10T00:25:31+00:00", description="가입일자"),
     *              @OA\Property(property="updatedAt", type="string", format="timezone",  example="2021-03-10T00:25:31+00:00", description="수정일자"),
     *          )
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     */

    /**
     * 회원 정보
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function info()
    {
        // 회원정보
        return response()->json(CollectionLibrary::toCamelCase(collect(auth()->user())));
    }


    /**
     * @OA\Delete(
     *      path="/v1/member/auth",
     *      summary="로그아웃",
     *      description="회원 로그아웃",
     *      operationId="memberLogout",
     *      tags={"회원관련"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully logout"
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     */
    /**
     * 로그아웃
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        // 엑세스 토큰 제거
        auth()->user()->token()->revoke();

        // refesh token revoke
//        $refreshTokenRepository = app('Laravel\Passport\RefreshTokenRepository');
//        $refreshTokenRepository->revokeRefreshTokensByAccessTokenId(auth()->user()->token()->id);

        return response()->json([
            'message' => __('common.logout')
        ], 200);
    }

    /**
     * @OA\Post(
     *      path="/v1/email/verificationResend",
     *      summary="이메일 인증 재발송",
     *      description="회원 이메일 인증 재발송",
     *      operationId="memberVerifyEmailReSend",
     *      tags={"회원관련"},
     *      @OA\Response(
     *          response=200,
     *          description="send verify email",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="send verify email"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed registered",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                  @OA\Property(
     *                      property="statusCode",
     *                      type="object",
     *                      @OA\Property(
     *                          property="110411",
     *                          type="object",
     *                          description="짧은 시간내에 잦은 요청으로 인해 재발송 불가 합니다.",
     *                          @OA\Property(
     *                              property="message",
     *                              type="string",
     *                          ),
     *                      ),
     *                      @OA\Property(
     *                          property="110402",
     *                          type="object",
     *                          description="이미 인증된 회원입니다.",
     *                          @OA\Property(
     *                              property="message",
     *                              type="string",
     *                          ),
     *                      ),
     *                  )
     *              ),
     *          )
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
    public function verificationResend(Request $request)
    {

        // 짧은 시간내에 잦은 요청으로 인해 재발송 불가 합니다.
        if (!VerifyEmailCheck::dispatch(auth()->user())) {
            return response()->json(getResponseError(110411), 422);
        }

        // // 이미 인증된 회원입니다.
        if (!VerifyEmail::dispatch(auth()->user())) {
            return response()->json(getResponseError(110402), 422);
        }

        return response()->json([
            'message' => __('common.verification_resend')
        ], 200);

    }

    /**
     * @OA\Get(
     *      path="/v1/email/member.regist/{id}?expires={expires}&hash={hash}&signature={signature}",
     *      summary="이메일 인증",
     *      description="회원 이메일 인증",
     *      operationId="memberVerifyEmail",
     *      tags={"회원관련"},
     *      @OA\Response(
     *          response=200,
     *          description="send verify email",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="send verify email"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed registered",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                  @OA\Property(
     *                      property="statusCode",
     *                      type="object",
     *                      @OA\Property(
     *                          property="110401",
     *                          type="object",
     *                          description="잘못된 인증 방식입니다.",
     *                          @OA\Property(
     *                              property="message",
     *                              type="string",
     *                          ),
     *                      ),
     *                      @OA\Property(
     *                          property="110402",
     *                          type="object",
     *                          description="이미 인증된 회원입니다.",
     *                          @OA\Property(
     *                              property="message",
     *                              type="string",
     *                          ),
     *                      ),
     *                  )
     *              ),
     *          )
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
        $signCode = SignedCodes::getBySignCode($exp, $request->id, $request->hash, $request->signature)->select('id')->first();

        // 가상 서명키 유효성 체크
        if ($request->hasValidSignature() && $signCode && $signCode['id']) {

            $member = User::find($request->id);

            // 인증되지 않은 경우
            if (is_null($member->email_verified_at)) {
                $member->email_verified_at = carbon::now();
                $member->save();
            } else {
                // 이미 인증된 회원입니다.
                return response()->json(getResponseError(110402), 422);
            }

            // 가상 서명키 제거
            $signCode->delete();
        } else {
            return response()->json(getResponseError(110401), 422);
        }

        return response()->json([
            'message' => __('common.verified'),
            'member' => $member
        ], 200);
    }


    /**
     * @OA\Post(
     *      path="/v1/member/password",
     *      summary="비밀번호 검증",
     *      description="회원 비밀번호 검증",
     *      operationId="memberPasswordVerify",
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
     *          response=200,
     *          description="올바른 정보입니다.",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="This is the correct information."),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed registered",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                  @OA\Property(
     *                      property="statusCode",
     *                      type="object",
     *                      allOf={
     *                          @OA\Schema(
     *                              @OA\Property(property="100001", ref="#/components/schemas/RequestResponse/properties/100001"),
     *                              @OA\Property(property="110311", ref="#/components/schemas/RequestResponse/properties/110311"),
     *                          ),
     *                      }
     *                  ),
     *              ),
     *          )
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
            return response()->json(getResponseError(110311), 422);
        }

        return response()->json([
            'message' => __('common.correct')
        ], 200);
    }

    /**
     * @OA\Patch(
     *      path="/v1/member",
     *      summary="회원정보 수정",
     *      description="회원 정보 수정",
     *      operationId="memberInfoModify",
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
     *          response=200,
     *          description="변경되었습니다.",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="This is the correct information."),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed registered",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                  @OA\Property(
     *                      property="statusCode",
     *                      type="object",
     *                      allOf={
     *                          @OA\Schema(
     *                              @OA\Property(property="100001", ref="#/components/schemas/RequestResponse/properties/100001"),
     *                              @OA\Property(property="100053", ref="#/components/schemas/RequestResponse/properties/100053"),
     *                              @OA\Property(property="110311", ref="#/components/schemas/RequestResponse/properties/110311"),
     *                          ),
     *                      }
     *                  ),
     *              ),
     *          )
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
    public function modify(ModifyMemberRequest $request)
    {

        if (!$this::funcCheckPassword($request->password)) {
            return response()->json(getResponseError(110311, 'password'), 422);
        }

        $member = auth()->user();
        $member->name = $request->name;
        $member->save();

        return response()->json([
            'message' => __('common.modified')
        ], 200);
    }


    /**
     * @OA\Patch(
     *      path="/v1/member/password",
     *      summary="비밀번호 변경",
     *      description="회원 비밀번호 변경",
     *      operationId="memberPwdModify",
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
     *          response=200,
     *          description="변경되었습니다.",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="This is the correct information."),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="비밀 번호가 올바르지 않습니다.",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                  @OA\Property(
     *                      property="statusCode",
     *                      type="object",
     *                      allOf={
     *                          @OA\Schema(
     *                              @OA\Property(property="100001", ref="#/components/schemas/RequestResponse/properties/100001"),
     *                              @OA\Property(property="100011", ref="#/components/schemas/RequestResponse/properties/100011"),
     *                              @OA\Property(property="100063", ref="#/components/schemas/RequestResponse/properties/100063"),
     *                          ),
     *                          @OA\Schema(ref="#/components/schemas/passwordPattern"),
     *                      }
     *                  ),
     *              ),
     *          )
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
            return response()->json(getResponseError(110311, 'password'), 422);
        }

        // 기존 비밀번호와 변경할 비밀번호가 같을 경우
        if (hash::check($request->changePassword, auth()->user()->password)) {
            return response()->json(getResponseError(110100, 'changePassword'), 422);
        }

        // 비밀번호 체크
        $checkPwdRes = $this->checkPasswordPattern($request->changePassword, auth()->user()->email);
        if ($checkPwdRes !== true) {
            return response()->json($checkPwdRes, 422);
        }

        $member = auth()->user();
        $member->password = hash::make($request->changePassword);
        $member->save();

        return response()->json([
            'message' => __('common.changed')
        ], 200);
    }




    /**
     * @OA\Post(
     *      path="/v1/member/passwordResetSendLink",
     *      summary="비밀번호 찾기",
     *      description="회원 비밀번호 찾기 - 변경을 위한 링크 발송",
     *      operationId="memberPasswordResetSendLink",
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
     *          response=200,
     *          description="비밀번호 변경 링크가 메일로 발송되었습니다.",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed registered",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                  @OA\Property(
     *                      property="statusCode",
     *                      type="object",
     *                      allOf={
     *                          @OA\Schema(
     *                              @OA\Property(property="100001", ref="#/components/schemas/RequestResponse/properties/100001"),
     *                              @OA\Property(property="100021", ref="#/components/schemas/RequestResponse/properties/100021"),
     *                              @OA\Property(property="100101", ref="#/components/schemas/RequestResponse/properties/100101"),
     *                          ),
     *                      }
     *                  ),
     *              ),
     *          )
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
        $member = User::where('email', $request->email)->first();

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

        return response()->json([
            'message' => __('common.verification_send')
        ], 200);
    }



    /**
     * @OA\Post(
     *      path="/v1/member/checkChangePwdAuth",
     *      summary="비밀번호 찾기 Token 인증",
     *      description="비밀번호 찾기 - 변경을 위한 링크의 값 유효성 체크",
     *      operationId="memberPasswordResetLinkAuth",
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
     *          response=200,
     *          description="정상적인 인증방식입니다.",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed registered",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                  @OA\Property(
     *                      property="statusCode",
     *                      type="object",
     *                      allOf={
     *                          @OA\Schema(
     *                              @OA\Property(property="100001", ref="#/components/schemas/RequestResponse/properties/100001"),
     *                              @OA\Property(property="100021", ref="#/components/schemas/RequestResponse/properties/100021"),
     *                              @OA\Property(property="100101", ref="#/components/schemas/RequestResponse/properties/100101"),
     *                          ),
     *                          @OA\Schema(
     *                              @OA\Property(
     *                                  property="100005",
     *                                  type="object",
     *                                  description="일치하는 정보가 없습니다.",
     *                                  @OA\Property(
     *                                      property="message",
     *                                      type="string",
     *                                  ),
     *                              ),
     *                              @OA\Property(
     *                                  property="100501",
     *                                  type="object",
     *                                  description="잘못된 인증방식이거나 token의 유효시간이 지났습니다.",
     *                                  @OA\Property(
     *                                      property="message",
     *                                      type="string",
     *                                  ),
     *                              ),
     *                          )
     *                      }
     *                  ),
     *              ),
     *          )
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
            return response()->json(getResponseError(100005), 422);
        }

        // 회원정보
        $member = User::where('email', $request->email)->first();

        // Token 유효성 체크
        if (!Password::tokenExists($member, $request->token)) {
            return response()->json(getResponseError(100501), 422);
        }

        return response()->json([
            'message' => __('common.verified')
        ], 200);
    }


    /**
     * @OA\Patch(
     *      path="/v1/member/passwordReset",
     *      summary="비밀번호 찾기 - 변경",
     *      description="비밀번호 찾기를 통한 변경 url을 통한 후 비밀번호 변경",
     *      operationId="memberPasswordReset",
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
     *          response=200,
     *          description="변경되었습니다.",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed registered",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                  @OA\Property(
     *                      property="statusCode",
     *                      type="object",
     *                      allOf={
     *                          @OA\Schema(
     *                              @OA\Property(property="100001", ref="#/components/schemas/RequestResponse/properties/100001"),
     *                              @OA\Property(property="100011", ref="#/components/schemas/RequestResponse/properties/100011"),
     *                              @OA\Property(property="100021", ref="#/components/schemas/RequestResponse/properties/100021"),
     *                              @OA\Property(property="100063", ref="#/components/schemas/RequestResponse/properties/100063"),
     *                              @OA\Property(property="100101", ref="#/components/schemas/RequestResponse/properties/100101"),
     *                          ),
     *                          @OA\Schema(
     *                              @OA\Property(
     *                                  property="100005",
     *                                  type="object",
     *                                  description="일치하는 정보가 없습니다.",
     *                                  @OA\Property(
     *                                      property="message",
     *                                      type="string",
     *                                  ),
     *                              ),
     *                              @OA\Property(
     *                                  property="100501",
     *                                  type="object",
     *                                  description="잘못된 인증방식이거나 token의 유효시간이 지났습니다.",
     *                                  @OA\Property(
     *                                      property="message",
     *                                      type="string",
     *                                  ),
     *                              ),
     *                          ),
     *                          @OA\Schema(ref="#/components/schemas/passwordPattern")
     *                      }
     *                  ),
     *              ),
     *          )
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
            return response()->json(getResponseError(100005), 422);
        }

        // 회원정보
        $member = User::where('email', $request->email)->first();

        // Token 유효성 체크
        if (!Password::tokenExists($member, $request->token)) {
            return response()->json(getResponseError(100501), 422);
        }

        // 비밀번호 체크
        $checkPwdRes = $this->checkPasswordPattern($request->password, $request->email);
        if ($checkPwdRes !== true) {
            return response()->json($checkPwdRes, 422);
        }

        // 비밀번호 변경
        $member->password = hash::make($request->password);
        $member->save();

        // 비밀번호 변경 Token 삭제
        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json([
            'message' => __('common.changed')
        ], 200);
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
            return getResponseError(110101, 'password');
        } else if (!$chkPasswordRes['continue']) {  // 연속된 문자, 동일한 문자 연속 체크
            return getResponseError(110102, 'password');
        } else if (!$chkPasswordRes['empty']) { // 공백 문자 체크
            return getResponseError(110103, 'password');
        }

        /**
         * 비밀번호와 아이디 동일 여부 체크
         */
        if (isset($email)) {
            $chkPwdSameIdRes = checkPwdSameId($pwd, $email);
            if (!$chkPwdSameIdRes) {
                return getResponseError(110114, 'password');
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
