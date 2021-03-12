<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreMembersRequest;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Flight;
use Validator;
use Carbon\Carbon;
use Hash;


class MemberController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['register']]);
    }

    /**
     * @OA\Post(
     *  path="/v1/members",
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
     *                      example="The name field is required.",
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
     *                      example="The password special letters, alphabets, and numbers must be combined.",
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
     *                      example="The password cannot be used 4 times consecutively with the same characters as consecutive characters.",
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
     *                      example="The password cannot contain space characters.",
     *                  ),
     *              ),
     *          )
     *       ),
     *    )
     *  )
     * )
     */

    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(StoreMembersRequest $request) {//
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

        print_r($request->all());




//        var_dump(checkPwdPattern('aaaa'));
//        var_dump(checkPwdPattern('aabb'));
//        var_dump(checkPwdPattern('adbc'));
//        var_dump(checkPwdPattern('dcba'));
//        var_dump(checkPwdPattern('1234'));
//        var_dump(checkPwdPattern('1111'));
//        var_dump(checkPwdPattern('1122'));
//        var_dump(checkPwdPattern('1324'));
//        var_dump(checkPwdPattern('4321'));

//        var_dump(similar_text('abcde', 'a1b3e', $perc));
//        var_dump($perc);

//        var_dump($aaa);




//        var_dump(preg_match('/[0-9]{4}[a-z]{4}/i', $request->password));
//        if (preg_match("/(\w)\\1\\1\\1/", $request->password)) {
//            echo 'asdasd';
//        }
//        $member = User::create(array_merge(
//            $request->all(),
//            ['password' => bcrypt($request->password)]
//        ));
//
//        return response()->json([
//            'message' => __('member.registered'),
//            'member' => $member
//        ], 200);
    }


//    /**
//     * Refresh a token.
//     *
//     * @return \Illuminate\Http\JsonResponse
//     */
//    public function refresh() {
//        return $this->createNewToken(auth()->refresh());
//    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile() {
        $aaa = Flight::where('name', '1234124')->first()->toArray();
//
////        echo Carbon::now()->timezone('Asia/Seoul')->tz('UTC');
////        print_r($aaa['reg_date']);
////        echo Carbon::createFromFormat($aaa['reg_date']);
//        echo $aaa['reg_date'] . "\r\n";
//
//        $bbb = $aaa['reg_date']->tz('UTC') . "\r\n";
//        print_r($bbb);
//
////        echo $bbb->diff($aaa['reg_date']);
//
//
        return response()->json($aaa);

//        return response()->json(auth()->user());
    }

}
