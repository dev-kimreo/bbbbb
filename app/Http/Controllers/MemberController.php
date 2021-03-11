<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreMembersRequest;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Flight;
use Validator;
use Carbon\Carbon;


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
     *                  @OA\Property(
     *                      property="key",
     *                      type="string",
     *                      description="name",
     *                      example="name",
     *                  ),
     *                  @OA\Property(
     *                      property="message",
     *                      type="string",
     *                      description="name 필드는 필수값입니다.",
     *                      example="The name field is required.",
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
    public function register(StoreMembersRequest $request) {
        echo $request->password;

//
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
