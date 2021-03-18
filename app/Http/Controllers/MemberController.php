<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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



class MemberController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['register', 'verification']]);
    }

    /**
     * @OA\Post(
     *  path="/v1/member",
     *  summary="회원가입",
     *  description="회원가입",
     *  operationId="memberSignIn",
     *  tags={"Members"},
     *  @OA\RequestBody(
     *    required=true,
     *    description="",
     *    @OA\JsonContent(
     *       required={"name","email", "password", "passwordConfirmation"},
     *       @OA\Property(property="name", type="string", example="홍길동", description="이름"),
     *       @OA\Property(property="email", type="string", format="email", example="abcd@davinci.com", description="이메일"),
     *       @OA\Property(property="password", type="string", format="password", example="1234qwer", description="비밀번호"),
     *       @OA\Property(property="passwordConfirmation", type="string", format="password", example="1234qwer", description="비밀번호 재확인"),
     *    ),
     *  ),
     *  @OA\Response(
     *    response=200,
     *    description="successfully registered",
     *    @OA\JsonContent(
     *       @OA\Property(property="no", type="integer", example="14", description="회원번호"),
     *       @OA\Property(property="name", type="string", example="홍길동", description="이름"),
     *       @OA\Property(property="email", type="string", format="email", example="abcd@davinci.com", description="이메일"),
     *       @OA\Property(property="emailVerifiedDate", type="string", format="timezone", example="null", description="이메일 인증일자"),
     *       @OA\Property(property="regDate", type="string", format="timezone", example="2021-03-10T00:25:31+00:00", description="가입일자"),
     *       @OA\Property(property="uptDate", type="string", format="timezone",  example="2021-03-10T00:25:31+00:00", description="수정일자"),
     *    )
     *  ),
     *  @OA\Response(
     *    response=422,
     *    description="failed registered",
     *    @OA\JsonContent(
     *       @OA\Property(
     *          property="errors",
     *          type="object",
     *          @OA\Property(
     *              property="statusCode",
     *              type="object",
     *              @OA\Property(
     *                  property="10000",
     *                  type="object",
     *                  description="이미 사용중인 email 이 있습니다.",
     *                  @OA\Property(
     *                      property="key",
     *                      type="string",
     *                      description="email",
     *                      example="email",
     *                  ),
     *                  @OA\Property(
     *                      property="message",
     *                      type="string",
     *                  ),
     *              ),
     *              @OA\Property(
     *                  property="10101",
     *                  type="object",
     *                  description="password 는 특수문자, 알파벳, 숫자 3가지가 조합되어야 합니다.",
     *                  @OA\Property(
     *                      property="key",
     *                      type="string",
     *                      description="password",
     *                      example="password",
     *                  ),
     *                  @OA\Property(
     *                      property="message",
     *                      type="string",
     *                  ),
     *              ),
     *              @OA\Property(
     *                  property="10102",
     *                  type="object",
     *                  description="password 는 연속 된 문자와 동일한 문자로 4 회 연속 사용할 수 없습니다.",
     *                  @OA\Property(
     *                      property="key",
     *                      type="string",
     *                      description="password",
     *                      example="password",
     *                  ),
     *                  @OA\Property(
     *                      property="message",
     *                      type="string",
     *                  ),
     *              ),
     *              @OA\Property(
     *                  property="10103",
     *                  type="object",
     *                  description="password 는 공백문자를 포함할 수 없습니다.",
     *                  @OA\Property(
     *                      property="key",
     *                      type="string",
     *                      description="password",
     *                      example="password",
     *                  ),
     *                  @OA\Property(
     *                      property="message",
     *                      type="string",
     *                  ),
     *              ),
     *              @OA\Property(
     *                  property="10111",
     *                  type="object",
     *                  description="password 는 email과 4자 이상 동일 할 수 없습니다.",
     *                  @OA\Property(
     *                      property="key",
     *                      type="string",
     *                      description="password",
     *                      example="password",
     *                  ),
     *                  @OA\Property(
     *                      property="message",
     *                      type="string",
     *                  ),
     *              ),
     *          )
     *       ),
     *    )
     *  )
     * )
     */

    /**
     * 회원가입
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(StoreMembersRequest $request) {
        /**
         * 비밀번호 패턴 체크
         */
        $chkPasswordRes = checkPwdPattern($request->password);
        if (!$chkPasswordRes['combination']) {  // 특수문자, 문자, 숫자 포함 체크
            return response()->json(getResponseError(10101, 'password'), 422);
        } else if (!$chkPasswordRes['continue']) {  // 연속된 문자, 동일한 문자 연속 체크
            return response()->json(getResponseError(10102, 'password'), 422);
        } else if (!$chkPasswordRes['empty']) { // 공백 문자 체크
            return response()->json(getResponseError(10103, 'password'), 422);
        }

        /**
         * 비밀번호와 아이디 동일 여부 체크
         */
        $chkPwdSameIdRes = checkPwdSameId($request->password, $request->email);
        if (!$chkPwdSameIdRes) {
            return response()->json(getResponseError(10111, 'password'), 422);
        }

        $member = User::create(array_merge(
            $request->all(),
            ['password' => hash::make($request->password)]
        ));

        VerifyEmail::dispatch($member);

        return response()->json([
            'message' => __('member.registered'),
            'member' => $member
        ], 200);
    }


    /**
     * @OA\Get(
     *  path="/v1/member",
     *  summary="회원정보",
     *  description="회원정보",
     *  operationId="memberInfo",
     *  tags={"Members"},
     *  @OA\Response(
     *    response=200,
     *    description="successfully registered",
     *    @OA\JsonContent(
     *       @OA\Property(property="no", type="integer", example="14", description="회원번호"),
     *       @OA\Property(property="name", type="string", example="홍길동", description="이름"),
     *       @OA\Property(property="email", type="string", format="email", example="abcd@davinci.com", description="이메일"),
     *       @OA\Property(property="emailVerifiedDate", type="string", format="timezone", example="null", description="이메일 인증일자"),
     *       @OA\Property(property="regDate", type="string", format="timezone", example="2021-03-10T00:25:31+00:00", description="가입일자"),
     *       @OA\Property(property="uptDate", type="string", format="timezone",  example="2021-03-10T00:25:31+00:00", description="수정일자"),
     *    )
     *  ),
     * security={{
     *     "davinci_auth":{}
     *   }}
     * )
     */

    /**
     * 회원 정보
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function info() {
        // 회원정보
        return response()->json(auth()->user());
    }


    /**
     * @OA\Delete(
     *  path="/v1/member/auth",
     *  summary="회원 로그아웃",
     *  description="회원 로그아웃",
     *  operationId="memberLogout",
     *  tags={"Members"},
     *  @OA\Response(
     *    response=200,
     *    description="successfully logout"
     *  ),
     * security={{
     *     "davinci_auth":{}
     *   }}
     * )
     */
    /**
     * 로그아웃
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        // 엑세스 토큰 제거
        auth()->user()->token()->revoke();

        // refesh token revoke
//        $refreshTokenRepository = app('Laravel\Passport\RefreshTokenRepository');
//        $refreshTokenRepository->revokeRefreshTokensByAccessTokenId(auth()->user()->token()->id);

        return response()->json([
            'message' => __('member.logout')
        ], 200);
    }

    /**
     * @OA\Post(
     *  path="/v1/email/verificationResend",
     *  summary="회원 이메일 인증 재발송",
     *  description="회원 이메일 인증 재발송",
     *  operationId="memberVerifyEmailReSend",
     *  tags={"Members"},
     *  @OA\Response(
     *    response=200,
     *    description="send verify email",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="send verify email"),
     *    )
     *  ),
     *  @OA\Response(
     *    response=422,
     *    description="failed registered",
     *    @OA\JsonContent(
     *       @OA\Property(
     *          property="errors",
     *          type="object",
     *          @OA\Property(
     *              property="statusCode",
     *              type="object",
     *              @OA\Property(
     *                  property="10411",
     *                  type="object",
     *                  description="짧은 시간내에 잦은 요청으로 인해 재발송 불가 합니다.",
     *                  @OA\Property(
     *                      property="message",
     *                      type="string",
     *                  ),
     *              ),
     *              @OA\Property(
     *                  property="10421",
     *                  type="object",
     *                  description="이미 인증된 회원입니다.",
     *                  @OA\Property(
     *                      property="message",
     *                      type="string",
     *                  ),
     *              ),
     *          )
     *       ),
     *    )
     *  )
     * ),
     * security={{
     *     "davinci_auth":{}
     *   }}
     * )
     */
    /**
     * 회원 인증 메일 재발송
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verificationResend(Request $request) {
        // 짧은 시간내에 잦은 요청으로 인해 재발송 불가 합니다.
        if ( !VerifyEmailCheck::dispatch(auth()->user()) ) {
            return response()->json(getResponseError(10411), 422);
        }

        // // 이미 인증된 회원입니다.
        if ( !VerifyEmail::dispatch(auth()->user()) ) {
            return response()->json(getResponseError(10421), 422);
        }

        return response()->json([
            'message' => __('member.verification_resend')
        ], 200);

    }

    /**
     * @OA\Get(
     *  path="/verification.verify/{id}?expires={expires}&hash={hash}&signature={signature}",
     *  summary="회원 이메일 인증",
     *  description="회원 이메일 인증",
     *  operationId="memberVerifyEmail",
     *  tags={"Members"},
     *  @OA\Response(
     *    response=200,
     *    description="send verify email",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="send verify email"),
     *    )
     *  ),
     *  @OA\Response(
     *    response=422,
     *    description="failed registered",
     *    @OA\JsonContent(
     *       @OA\Property(
     *          property="errors",
     *          type="object",
     *          @OA\Property(
     *              property="statusCode",
     *              type="object",
     *              @OA\Property(
     *                  property="10401",
     *                  type="object",
     *                  description="잘못된 인증 방식입니다.",
     *                  @OA\Property(
     *                      property="message",
     *                      type="string",
     *                  ),
     *              ),
     *              @OA\Property(
     *                  property="10421",
     *                  type="object",
     *                  description="이미 인증된 회원입니다.",
     *                  @OA\Property(
     *                      property="message",
     *                      type="string",
     *                  ),
     *              ),
     *          )
     *       ),
     *    )
     *  ),
     * security={{
     *     "davinci_auth":{}
     *   }}
     * )
     */
    /**
     * 회원 메일 인증
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verification(Request $request){
        $signExists = SignedCodes::where('name', explode('/', $request->path())[0])
            ->where('name_key', $request->id)
            ->where('hash', $request->hash)
            ->where('sign', $request->signature)
            ->exists();

        // 가상 서명키 유효성 체크
        if ($request->hasValidSignature() && $signExists) {

            $member = User::find($request->id);

            // 인증되지 않은 경우
            if ( is_null($member->emailVerifiedDate) ) {
                $member->emailVerifiedDate = carbon::now();
                $member->save();
            } else {
                // 이미 인증된 회원입니다.
                return response()->json(getResponseError(10421), 422);
            }

            // 가상 서명키 제거
            SignedCodes::where('name', explode('/', $request->path())[0])
                            ->where('name_key', $request->id)
                            ->delete();
        } else {
            return response()->json(getResponseError(10401), 422);
        }

        return response()->json([
            'message' => __('member.registered'),
            'member' => $member
        ], 200);
    }


    /**
     * @OA\Post(
     *  path="/v1/member/password",
     *  summary="회원 비밀번호 검증",
     *  description="회원 비밀번호 검증",
     *  operationId="memberPasswordVerify",
     *  tags={"Members"},
     *  @OA\RequestBody(
     *    required=true,
     *    description="",
     *    @OA\JsonContent(
     *       required={"password"},
     *       @OA\Property(property="password", type="string", format="password", example="1234qwer", description="비밀번호"),
     *    ),
     *  ),
     *  @OA\Response(
     *    response=200,
     *    description="올바른 정보입니다.",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="This is the correct information."),
     *    )
     *  ),
     *  @OA\Response(
     *    response=422,
     *    description="failed registered",
     *    @OA\JsonContent(
     *       @OA\Property(
     *          property="errors",
     *          type="object",
     *          @OA\Property(
     *              property="statusCode",
     *              type="object",
     *              @OA\Property(
     *                  property="10311",
     *                  type="object",
     *                  description="로그인 정보가 올바르지 않습니다.",
     *                  @OA\Property(
     *                      property="message",
     *                      type="string",
     *                  ),
     *              ),
     *          )
     *       ),
     *    )
     *  )
     * ),
     * security={{
     *     "davinci_auth":{}
     *   }}
     * )
     */
    /**
     * 비밀번호 검증
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkPassword(CheckPwdMemberRequest $request) {
//        if ( !hash::check($request->password, auth()->user()->password) ){
//            return response()->json(getResponseError(10311), 422);
//        }

        return response()->json([
            'message' => __('member.correct')
        ], 200);
    }

    /**
     * @OA\Patch(
     *  path="/v1/member",
     *  summary="회원 정보 수정",
     *  description="회원 정보 수정",
     *  operationId="memberInfoModify",
     *  tags={"Members"},
     *  @OA\RequestBody(
     *    required=true,
     *    description="",
     *    @OA\JsonContent(
     *       required={"name", "password"},
     *       @OA\Property(property="name", type="string", example="홍길동", description="변경할 이름"),
     *       @OA\Property(property="password", type="string", format="password", example="1234qwer", description="비밀번호"),
     *    ),
     *  ),
     *  @OA\Response(
     *    response=200,
     *    description="변경되었습니다.",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="This is the correct information."),
     *    )
     *  ),
     *  @OA\Response(
     *    response=422,
     *    description="비밀 번호가 올바르지 않습니다.",
     *    @OA\JsonContent(
     *       @OA\Property(
     *          property="errors",
     *          type="object",
     *          @OA\Property(
     *              property="password",
     *              type="string",
     *              example="The password is incorrect."
     *          )
     *       ),
     *    )
     *  )
     * ),
     * security={{
     *     "davinci_auth":{}
     *   }}
     * )
     */

    /**
     * 회원 정보 수정
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function modify(ModifyMemberRequest $request) {
        $member = auth()->user();
        $member->name = $request->name;
        $member->save();

        return response()->json([
            'message' => __('member.changed')
        ], 200);
    }


    /**
     * @OA\Patch(
     *  path="/v1/member/password",
     *  summary="회원 비밀번호 변경",
     *  description="회원 비밀번호 변경",
     *  operationId="memberPwdModify",
     *  tags={"Members"},
     *  @OA\RequestBody(
     *    required=true,
     *    description="",
     *    @OA\JsonContent(
     *       required={"password", "changePassword", "passwordConfirmation"},
     *       @OA\Property(property="password", type="string", format="password", example="1234qwer", description="기존 비밀번호"),
     *       @OA\Property(property="changePassword", type="string", format="password", example="1234qwer11", description="변경할 비밀번호"),
     *       @OA\Property(property="passwordConfirmation", type="string", format="password", example="1234qwer11", description="변경할 비밀번호 확인"),
     *    ),
     *  ),
     *  @OA\Response(
     *    response=200,
     *    description="변경되었습니다.",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="This is the correct information."),
     *    )
     *  ),
     *  @OA\Response(
     *    response=422,
     *    description="비밀 번호가 올바르지 않습니다.",
     *    @OA\JsonContent(
     *       @OA\Property(
     *          property="errors",
     *          type="object",
     *          @OA\Property(
     *              property="statusCode",
     *              type="object",
     *              @OA\Property(
     *                  property="10101",
     *                  type="object",
     *                  description="password 는 특수문자, 알파벳, 숫자 3가지가 조합되어야 합니다.",
     *                  @OA\Property(
     *                      property="key",
     *                      type="string",
     *                      description="password",
     *                      example="password",
     *                  ),
     *                  @OA\Property(
     *                      property="message",
     *                      type="string",
     *                  ),
     *              ),
     *              @OA\Property(
     *                  property="10102",
     *                  type="object",
     *                  description="password 는 연속 된 문자와 동일한 문자로 4 회 연속 사용할 수 없습니다.",
     *                  @OA\Property(
     *                      property="key",
     *                      type="string",
     *                      description="password",
     *                      example="password",
     *                  ),
     *                  @OA\Property(
     *                      property="message",
     *                      type="string",
     *                  ),
     *              ),
     *              @OA\Property(
     *                  property="10103",
     *                  type="object",
     *                  description="password 는 공백문자를 포함할 수 없습니다.",
     *                  @OA\Property(
     *                      property="key",
     *                      type="string",
     *                      description="password",
     *                      example="password",
     *                  ),
     *                  @OA\Property(
     *                      property="message",
     *                      type="string",
     *                  ),
     *              ),
     *              @OA\Property(
     *                  property="10111",
     *                  type="object",
     *                  description="password 는 email과 4자 이상 동일 할 수 없습니다.",
     *                  @OA\Property(
     *                      property="key",
     *                      type="string",
     *                      description="password",
     *                      example="password",
     *                  ),
     *                  @OA\Property(
     *                      property="message",
     *                      type="string",
     *                  ),
     *              ),
     *          )
     *       ),
     *    )
     *  )
     * ),
     * security={{
     *     "davinci_auth":{}
     *   }}
     * )
     */

    /**
     * 회원 비밀번호 변경
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function modifyPassword(ModifyMemberPwdRequest $request) {
        /**
         * 비밀번호 패턴 체크
         */
        $chkPasswordRes = checkPwdPattern($request->changePassword);
        if (!$chkPasswordRes['combination']) {  // 특수문자, 문자, 숫자 포함 체크
            return response()->json(getResponseError(10101, 'password'), 422);
        } else if (!$chkPasswordRes['continue']) {  // 연속된 문자, 동일한 문자 연속 체크
            return response()->json(getResponseError(10102, 'password'), 422);
        } else if (!$chkPasswordRes['empty']) { // 공백 문자 체크
            return response()->json(getResponseError(10103, 'password'), 422);
        }

        /**
         * 비밀번호와 아이디 동일 여부 체크
         */
        $chkPwdSameIdRes = checkPwdSameId($request->changePassword, auth()->user()->email);
        if (!$chkPwdSameIdRes) {
            return response()->json(getResponseError(10111, 'password'), 422);
        }

        $member = auth()->user();
        $member->password = hash::make($request->changePassword);
        $member->save();

        return response()->json([
            'message' => __('member.changed')
        ], 200);


    }




}
