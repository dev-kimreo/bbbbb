<?php

namespace App\Http\Controllers\Users;

use App\Events\Member\Login as LoginEvent;
use App\Events\Member\VerifyEmail;
use App\Events\Member\VerifyEmailCheck;
use App\Exceptions\QpickHttpException;
use App\Http\Controllers\AccessTokenController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Members\CheckChangePwdAuthRequest;
use App\Http\Requests\Members\CheckPwdMemberRequest;
use App\Http\Requests\Members\IndexRequest;
use App\Http\Requests\Members\ModifyMemberPwdRequest;
use App\Http\Requests\Members\PasswordResetRequest;
use App\Http\Requests\Members\PasswordResetSendLinkRequest;
use App\Http\Requests\Members\ShowRequest;
use App\Http\Requests\Members\StoreRequest;
use App\Http\Requests\Members\UpdateRequest;
use App\Http\Requests\Users\ActionLogRequest;
use App\Http\Requests\Users\LoginLogRequest;
use App\Http\Requests\Users\LoginLogStatRequest;
use App\Libraries\PaginationLibrary;
use App\Libraries\StringLibrary;
use App\Mail\QpickMailSender;
use App\Models\ActionLog;
use App\Models\SignedCode;
use App\Models\Users\User;
use App\Services\UserService;
use Auth;
use Cache;
use DB;
use Hash;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Password;

class UserController extends Controller
{
    protected User $user;
    protected SignedCode $signedCode;
    public string $exceptionEntity = "user";

    /**
     * Create a new AuthController instance.
     *
     * @param User $user
     * @param SignedCode $signedCode
     */
    public function __construct(User $user, SignedCode $signedCode)
    {
        $this->user = $user;
        $this->signedCode = $signedCode;
    }

    /**
     * @OA\Get(
     *      path="/v1/user",
     *      summary="회원정보 목록",
     *      description="회원정보 다건 열람",
     *      operationId="userList",
     *      tags={"회원관련"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={},
     *              @OA\Property(property="status", type="string", example="inactive", default="active", description="active:활성회원<br />inactive:휴면회원<br />deleted:탈퇴회원"),
     *              @OA\Property(property="startCreatedDate", type="datetime", example="2021-07-01T00:00:00+00:00", description="가입일 검색 시작일"),
     *              @OA\Property(property="endCreatedDate", type="datetime", example="2021-07-01T23:59:59+00:00", description="가입일 검색 종료일"),
     *              @OA\Property(property="startRegisteredDate", type="datetime", example="2021-07-01T00:00:00+00:00", description="전환일 검색 시작일"),
     *              @OA\Property(property="endRegisteredDate", type="datetime", example="2021-07-01T23:59:59+00:00", description="전환일 검색 종료일"),
     *              @OA\Property(property="grade[]", type="integer", example=1, description="회원 등급"),
     *              @OA\Property(property="id", type="integer", example=1, description="회원 번호"),
     *              @OA\Property(property="email", type="string", example="abcd@qpicki.com", description="ID(메일)"),
     *              @OA\Property(property="name", type="string", example="홍길동", description="이름"),
     *              @OA\Property(property="multiSearch", type="string", example="홍길동", description="전체 검색"),
     *              @OA\Property(property="advAgree", type="boolean", example="true", description="광고수신동의 여부<br/>(1:동의, 0:미동의)"),
     *              @OA\Property(property="activate", type="boolean", example="true", description="회원 상태 여부<br/>(1:정상, 0:휴면)"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/Pagination" ),
     *              @OA\Property(property="list", type="array",
     *                  @OA\Items(type="object",
     *                      allOf={
     *                          @OA\Schema(ref="#/components/schemas/User"),
     *                          @OA\Schema(
     *                              @OA\Property(property="authority", type="array",
     *                                  @OA\Items(type="object", ref="#/components/schemas/Authority")
     *                              )
     *                          ),
     *                      }
     *                  )
     *              ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated (비로그인)"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="forbidden (관리자가 아닌 상태에서 정보조회를 시도하는 상황 등)"
     *      )
     *  )
     *
     * 회원정보 목록
     *
     * @param IndexRequest $request
     * @return Collection
     */
    public function index(IndexRequest $request): Collection
    {
        // get model
        $user = $this->user;

        $status = $request->input('status') ?? 'active';
        if (in_array($status, ['active', 'inactive', 'deleted'])) {
            $user = $this->user::status($status);
        }

        $user = $user->with(['privacy', 'advAgree', 'sites', 'sites.userSolution', 'authority']);

        // set search conditions
        if ($s = $request->input('id')) {
            $user->where('id', $s);
        }

        if (is_array($s = $request->input('grade')) && !in_array(null, $s)) {
            $user->whereIn('grade', $s);
        }

        if ($s = $request->input('email')) {
            $user->whereHas('privacy', function (Builder $q) use ($s) {
                $q->where('email', 'like', '%' . StringLibrary::escapeSql($s) . '%');
            });
        }

        if ($s = $request->input('name')) {
            $user->whereHas('privacy', function (Builder $q) use ($s) {
                $q->where('name', $s);
            });
        }

        if ($s = $request->input('multi_search')) {
            // 통합검색
            $user->where(function ($q) use ($s) {
                $q->whereHas('privacy', function (Builder $q) use ($s) {
                    $q->where('email', 'like', '%' . StringLibrary::escapeSql($s) . '%');
                    $q->orWhere('name', $s);
                });

                if (is_numeric($s)) {
                    $q->orWhere('id', $s);
                }
            });
        }

        if ($s = $request->input('start_created_date')) {
            $s = Carbon::parse($s);
            $user->where('created_at', '>=', $s);
        }

        if ($s = $request->input('end_created_date')) {
            $s = Carbon::parse($s);
            $user->where('created_at', '<=', $s);
        }

        if ($s = $request->input('start_registered_date')) {
            $s = Carbon::parse($s);
            $user->where('registered_at', '>=', $s);
        }

        if ($s = $request->input('end_registered_date')) {
            $s = Carbon::parse($s);
            $user->where('registered_at', '<=', $s);
        }

        // 광고수신동의
        if (strlen($s = $request->input('adv_agree'))) {
            switch ($s) {
                case 1:
                    $user->whereHas('advAgree', function (Builder $q) use ($s) {
                        $q->where('agree', $s);
                    });
                    break;
                default:
                    $user->whereHas('advAgree', function (Builder $q) use ($s) {
                        $q->where('agree', 1);
                    }, '!=');
                    break;
            }
        }

        // 회원 상태 구분
        if (strlen($s = $request->input('activate'))) {
            if ($s) {
                $user->orWhereNull('inactivated_at');
            } else {
                $user->orWhereNotNull('inactivated_at');
            }
        }

        // set pagination information
        $pagination = PaginationLibrary::set($request->input('page'), $user->count(), $request->input('per_page'));

        // get data
        $data = $user->skip($pagination['skip'])->take($pagination['perPage'])->get();

        $data->each(function(&$item){
            $item->name = $item->privacy->name ?? null;
            $item->email = $item->privacy->email ?? null;
            unset($item->privacy);
        });

        // 백오피스인 경우, 관리자메모 추가
        if (Auth::hasAccessRightsToBackoffice()) {
            $data->makeVisible(['memo_for_managers']);
        }

        // result
        $result = [
            'header' => $pagination ?? [],
            'list' => $data ?? []
        ];

        return collect($result);
    }


    /**
     * @OA\Get(
     *      path="/v1/user/{id}",
     *      summary="회원정보 열람",
     *      description="회원정보 1건 열람",
     *      operationId="userInfo",
     *      tags={"회원관련"},
     *      @OA\RequestBody(
     *          required=true,
     *          description=""
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/User")
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated (비로그인)"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="forbidden (특정 회원이 타회원의 정보조회를 시도하는 상황 등)"
     *      )
     *  )
     *
     * 회원정보 단건열람
     *
     * @param int $id
     * @param ShowRequest $request
     * @return JsonResponse
     * @throws QpickHttpException
     */
    public function show(int $id, ShowRequest $request): JsonResponse
    {
        if ($id != Auth::id() && !Auth::hasAccessRightsToBackoffice()) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        $data = $this->getOne($id);
        return response()->json(collect($data));
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
     *
     * 회원가입
     *
     * @param StoreRequest $request
     * @return JsonResponse
     * @throws QpickHttpException
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $this->user::status('active');

        // 비밀번호 체크
        $this->chkCorrectPasswordPattern($request->input('password'), $request->input('email'));

        $this->user = $this->user::create(array_merge(
            $request->except('email', 'name', 'password'),
            ['password' => hash::make($request->input('password'))]
        ));

        $this->user->privacy()->create($request->all());

        $member = $this->getOne($this->user->id);
        VerifyEmail::dispatch($member);

        return response()->json(collect($member), 201);
    }


    /**
     * @OA\Patch(
     *      path="/v1/user/{id}",
     *      summary="회원정보 수정",
     *      description="회원 정보 수정",
     *      operationId="userInfoModify",
     *      tags={"회원관련"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"name", "password"},
     *              @OA\Property(property="name", type="string", example="홍길동", description="변경하고자 하는 이름"),
     *              @OA\Property(property="password", type="string", format="password", example="1234qwer", description="확인용 비밀번호<br>프론트에서 본 파라미터는 필수입력항목이며, 파라미터에 입력된 값이 기존에 설정된 비밀번호와 일치하지 않으면 403 오류가 발생한다.<br>백오피스에서는 이 파라미터를 생략할 수 있으며, 어떠한 값을 입력하여도 검사하지 않고 무시한다."),
     *              @OA\Property(property="memoForManagers", type="string", example="이 사용자는 어뷰징 기록이 있습니다.", description="관리자 메모 (백오피스에서만 사용 가능)"),
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
     *          response=403,
     *          description="forbidden"
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
     * @param int $id
     * @param UpdateRequest $request
     * @return JsonResponse
     * @throws QpickHttpException
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        if (!Auth::hasAccessRightsToBackoffice() && !$this::chkPasswordMatched($request->input('password'))) {
            throw new QpickHttpException(403, 'user.password.incorrect', 'password');
        }

        // update
        $this->user = User::find($id);
        $this->user->update($request->except(['email', 'password']));

        // privacy
        $this->user->privacy->update($request->only(['name']));

        //response
        $data = $this->getOne($id);
        return response()->json(collect($data), 201);
    }

    /**
     * @OA\delete(
     *      path="/v1/user/{id}",
     *      summary="회원정보 삭제(탈퇴)",
     *      description="회원탈퇴",
     *      operationId="userDelete",
     *      tags={"회원관련"},
     *      @OA\Response(
     *          response=204,
     *          description="deleted"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="not found"
     *      )
     *  )
     *
     * 회원정보 삭제 (탈퇴)
     *
     * @param int $id
     * @param Request $request
     * @param AccessTokenController $tokenController
     * @return Response
     * @throws QpickHttpException
     */
    public function destroy(int $id, Request $request, AccessTokenController $tokenController): Response
    {
        // validation
        if (!Auth::hasAccessRightsToBackoffice() && !$this::chkPasswordMatched($request->input('password'))) {
            throw new QpickHttpException(422, 'user.password.incorrect');
        }

        // delete
        UserService::withdrawal($this->user->findOrFail($id));

        // logout
        if(Auth::id() == $id) {
            $tokenController->logout();
        }

        // response
        return response()->noContent();
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
     * @param Request $request
     * @return Response
     * @throws QpickHttpException
     */
    public function resendVerificationEmail(Request $request): Response
    {
        // 짧은 시간내에 잦은 요청으로 인해 재발송 불가 합니다.
        if (!VerifyEmailCheck::dispatch(Auth::user())) {
            throw new QpickHttpException(422, 'email.too_many_send');
        }

        // // 이미 인증된 회원입니다.
        if (!VerifyEmail::dispatch(Auth::user())) {
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
     * @param Request $request
     * @return JsonResponse
     * @throws QpickHttpException
     */
    public function verification(Request $request): JsonResponse
    {
        $id = $request->route('user_id');
        $signCode = SignedCode::getBySignCode($id, $request->input('hash'), $request->input('signature'))->select('id')->first();

        // 가상 서명키 유효성 체크
        if (!$request->hasValidSignature()) {
            throw new QpickHttpException(422, 'email.failed_validation_signature');
        } else if (!$signCode || !$signCode['id']) {
            throw new QpickHttpException(422, 'email.not_found_sign_code');
        }

        // find user
        $member = $this->user::find($id);

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

        // response
        return response()->json(collect($member));
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
     * @param CheckPwdMemberRequest $request
     * @return Response
     * @throws QpickHttpException
     */
    public function checkPassword(CheckPwdMemberRequest $request): Response
    {
        if (!$this::chkPasswordMatched($request->input('password'))) {
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
     *          description="no content(modified)"
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
     * @param ModifyMemberPwdRequest $request
     * @return Response
     * @throws QpickHttpException
     */
    public function modifyPassword(ModifyMemberPwdRequest $request): Response
    {
        // 회원정보 가져오기
        $user = User::find(Auth::id());

        // 현재 패스워드 체크
        if (!$this::chkPasswordMatched($request->input('password'))) {
            throw new QpickHttpException(422, 'user.password.incorrect');
        }

        // 기존 비밀번호와 변경할 비밀번호가 같을 경우
        if (hash::check($request->input('change_password'), $user->password)) {
            throw new QpickHttpException(422, 'user.password.reuse');
        }

        // 비밀번호 체크
        $this->chkCorrectPasswordPattern($request->input('change_password'), $user->email);

        // 업데이트
        $user->setAttribute('password', hash::make($request->input('change_password')));
        $user->save();

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
     * @param PasswordResetSendLinkRequest $request
     * @return Response
     * @throws QpickHttpException
     */
    public function passwordResetSendLink(PasswordResetSendLinkRequest $request): Response
    {
        // 회원 정보
        $member = $this->user->whereHas('privacy', function (Builder $q) use ($request) {
            $q->where('email', $request->input('email'));
        })->first();

        if (!$member) {
            $inactiveUser = $this->user::status('inactive')->whereHas('privacy', function (Builder $q) use ($request) {
                $q->where('email', $request->input('email'));
            })->first();

            if ($inactiveUser) {
                throw new QpickHttpException(403, 'user.inactive');
            }

            throw new QpickHttpException(404, 'common.not_found');
        }

        $member->email = $member->privacy->email;

        $verifyToken = Password::createToken($member);
        $verifyUrl = config('services.qpick.domain') . config('services.qpick.verifyPasswordPath') . '?token=' . $verifyToken . "&email=" . $request->input('email');

        // 메일 발송
        $data = array(
            'url' => $verifyUrl
        );

        Mail::to($member->privacy)->send(new QpickMailSender('Users.VerifyPassword', $member, $data));

        // response
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
     * @param CheckChangePwdAuthRequest $request
     * @return Response
     * @throws QpickHttpException
     */
    public function changePwdVerification(CheckChangePwdAuthRequest $request): Response
    {
        // 비밀번호 재설정 Token 발행여부 체크
        $res = DB::table('password_resets')->where('email', $request->input('email'))->first();
        if (!$res) {
            // 일치하는 정보가 없습니다.
            throw new QpickHttpException(404, 'common.not_found');
        }

        // 회원정보
        $member = $this->user->whereHas('privacy', function (Builder $q) use ($request) {
            $q->where('email', $request->input('email'));
        })->first();

        if (!$member) {
            $inactiveUser = $this->user::status('inactive')->whereHas('privacy', function (Builder $q) use ($request) {
                $q->where('email', $request->input('email'));
            })->first();

            if ($inactiveUser) {
                throw new QpickHttpException(403, 'user.inactive');
            }

            throw new QpickHttpException(404, 'common.not_found');
        }

        // Token 유효성 체크
        if (!Password::tokenExists($member, $request->input('token'))) {
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
     * @param PasswordResetRequest $request
     * @return Response
     * @throws QpickHttpException
     */
    public function passwordReset(PasswordResetRequest $request): Response
    {
        // 비밀번호 재설정 Token 발행여부 체크
        $res = DB::table('password_resets')->where('email', $request->input('email'))->first();
        if (!$res) {
            throw new QpickHttpException(404, 'common.not_found');
        }

        // 회원정보
        $member = $this->user->whereHas('privacy', function (Builder $q) use ($request) {
            $q->where('email', $request->input('email'));
        })->first();

        if (!$member) {
            $inactiveUser = $this->user::status('inactive')->whereHas('privacy', function (Builder $q) use ($request) {
                $q->where('email', $request->input('email'));
            })->first();

            if ($inactiveUser) {
                throw new QpickHttpException(403, 'user.inactive');
            }

            throw new QpickHttpException(404, 'common.not_found');
        }

        // Token 유효성 체크
        if (!Password::tokenExists($member, $request->input('token'))) {
            throw new QpickHttpException(422, 'auth.incorrect_timeout');
        }

        // 비밀번호 체크
        $this->chkCorrectPasswordPattern($request->input('password'), $request->input('email'));

        // 비밀번호 변경
        $member->password = hash::make($request->input('password'));
        $member->save();

        // 비밀번호 변경 Token 삭제
        DB::table('password_resets')->where('email', $request->input('email'))->delete();

        return response()->noContent();
    }

    /**
     * @OA\Post(
     *      path="/v1/user/{id}/auth",
     *      summary="특정 회원 로그인",
     *      description="특정 회원 로그인",
     *      operationId="personalMemberLogin",
     *      tags={"회원관련"},
     *      @OA\Response(
     *          response=201,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="tokenType", type="string", example="Bearer"),
     *              @OA\Property(property="expiresIn", type="integer", example=600),
     *              @OA\Property(property="accessToken", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiZGY5YTI3OGE4YzRiZGM3YmM1NmQwZGFhNjQxYzNjYmRjYjEzM2ZkZGFkMWMxNzQ1YWU3ZDZiODM2NTI4ZDUwM2U0NjMyYWJhYjA2NWIxMTAiLCJpYXQiOiIxNjE2NTczODA0LjI2MTU2MyIsIm5iZiI6IjE2MTY1NzM4MDQuMjYxNTY3IiwiZXhwIjoiMTY0ODEwOTgwNC4yNDMxOTYiLCJzdWIiOiIxIiwic2NvcGVzIjpbXX0.WqgNN-8mX6hHehrkN77rGzzsDZOy-USzfyzqnuVnJLTSpTNlVK3FM0OpzGUnYOFwP2rCOoibAOcJX7xue2QeYtwu6QFWAPZIeJAi780ECPTdxcbTzAcWC9ckCQ0ryVKDk0cex2WAOvI3pOPFiKWvciAnqdKY7yvjcjFIxbvyZ5i-d0KoKZa6ucRjGU3msyky1pWwje1sYnkUE77kk8480TbnLPoHVe7PjRKwfsdUBVrYJPmdxJd-mh-OLL9c1UNHTqIPsn1PSpD-SdAxOfNwYrc8g-D1KBtsXv_GhO3L1L0lL7-jp_Ocmk_uFY8Z4Z89-7ZCNCrqHx4W1K2keNB8P8o7qI89BPWLBxDSYXJ8Pm0y6ajN_gvQRHPD9OzVPlpc212YwgWnt9ErbGeGK2cC1cyAZOikC84ye2jHGXs3dbozUrkBSkjWl8O-kU65uk3M7kiaB6BpIhE1sCbLOC55uCJSQInsInKQNUAvxlZNHSLeWwxaUP-kt-owYW9ResWNs10ofPkSIC31DFpx77eo98SeX4g5s69dDCVr1wvo_9lg1D8QOUvALNAR_ghN-O6ChvSWmxTvfVsiXIRaj413rLtSu1HgTSuBM0b-3DsjZrDEbHDYGnKNany0x-I3NXjUelKQwGb6JEixGmcnO5Yj7x5dCzCYVSd_EfeuHDxfhnk"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Client authentication failed"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed login"
     *      ),
     *      security={{
     *          "admin_auth":{}
     *      }}
     *  )
     * @param Request $request
     * @param $id
     * @return Collection
     */
    public function personalClientLogin(Request $request, $id): Collection
    {
        $this->user = $this->user->find($id);
        $token = $this->user->createToken('personal Login');

        $res = [];
        $res['token_type'] = 'Bearer';
        $res['expire_in'] = config('auth.personal_client.expire') * 60;
        $res['access_token'] = $token->accessToken;

        LoginEvent::dispatch($request, $id, $this->user->grade, 1, Auth::id());

        return collect($res);
    }

    /**
     * @OA\Get(
     *      path="/v1/user/{user_id}/action-log",
     *      summary="사용자 로그",
     *      description="특정 회원의 사용자 로그 조회",
     *      operationId="userActionLog",
     *      tags={"회원관련"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"email", "token", "password", "passwordConfirmation"},
     *              @OA\Property(property="startDate", type="date", example="2021-12-01 00:00:00", description="검색 시작일"),
     *              @OA\Property(property="endDate", type="date", example="2021-12-31 23:59:59", description="검색 종료일"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/Pagination"),
     *              @OA\Property(property="list", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="id", type="integer", example="7", description="로그 고유번호"),
     *                      @OA\Property(property="title", type="string", example="솔루션 연동완료", description="이벤트명"),
     *                      @OA\Property(property="ip", type="string(ipv4)", example="10.0.1.9", description="처리자 IP"),
     *                      @OA\Property(
     *                          property="createdAt", type="date", readOnly="true", format="date-time",
     *                          ref="#/components/schemas/Base/properties/created_at", example="2021-02-25 12:59:20",
     *                          description="처리일시"
     *                      ),
     *                      @OA\Property(property="changes", type="array of values", example="['name', 'memo']", description="처리내용"),
     *                      @OA\Property(property="user", type="object", ref="#/components/schemas/UserSimply", description="처리자 정보")
     *                  )
     *              ),
     *          )
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
    public function getActionLog(ActionLogRequest $request, int $user_id): array
    {
        $logs = ActionLog::query();

        // set search condition
        $logs->where('user_id', $user_id);

        $logs->whereBetween('created_at', [
            Carbon::parse($request->input('start_date')),
            Carbon::parse($request->input('end_date'))
        ]);

        // set pagination information
        $pg = PaginationLibrary::set($request->input('page'), $logs->count(), $request->input('per_page'));

        // get data
        $data = $logs->skip($pg['skip'])->take($pg['perPage'])->get();

        // Post Processing
        $data->each(function (&$item) {
            $item['changes'] = $item['properties']['changes'];
            unset($item['properties']);
        });

        // result
        return [
            'header' => $pagination ?? [],
            'list' => $data ?? []
        ];
    }

    /**
     * @OA\Get(
     *      path="/v1/user/{user_id}/login-log",
     *      summary="로그인 기록",
     *      description="특정 회원의 로그인 기록 조회",
     *      operationId="userLoginLog",
     *      tags={"회원관련"},
     *      @OA\Parameter(name="byManager", in="query", required=false,
     *          description="관리자의 로그인 기록만 조회할지 여부<br>1:관리자의 로그인 기록만 조회<br>>0:사용자 본인의 로그인 기록도 함께 조회",
     *          @OA\Schema(type="boolean", example="1", default=0),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/Pagination"),
     *              @OA\Property(property="list", type="array",
     *                  @OA\Items(type="object",
     *                      @OA\Property(property="id", type="integer", example="7"),
     *                      @OA\Property(property="ip", type="string(ipv4)", example="10.0.1.9"),
     *                      @OA\Property(
     *                          property="createdAt", type="string", readOnly="true", format="date-time",
     *                          ref="#/components/schemas/Base/properties/created_at", example="2021-02-25 12:59:20"
     *                      ),
     *                      @OA\Property(property="attemptedUser", type="object", ref="#/components/schemas/UserSimply")
     *                  )
     *              ),
     *          )
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
     *
     * 회원 로그인기록
     *
     * @param LoginLogRequest $request
     * @param int $user_id
     * @return array
     */
    public function getLoginLog(LoginLogRequest $request, int $user_id): array
    {
        // get a model
        $logs = ActionLog::loginLog($user_id);

        // set search condition
        $logs->whereBetween('created_at', [
            Carbon::parse($request->input('start_date')),
            Carbon::parse($request->input('end_date'))
        ]);

        if ($request->input('by_manager')) {
            $logs->whereRaw('user_id <> loggable_id');
        }

        // set pagination information
        $pagination = PaginationLibrary::set($request->input('page'), $logs->count(), $request->input('per_page'));

        // get data
        $data = $logs->skip($pagination['skip'])->take($pagination['perPage'])->get();
        $data->each(function (&$item) {
            $item['user_grade'] = $item['properties']['user_grade'];
            $item['attemptedUser'] = $item['user'];
            unset($item['properties'], $item['user']);
        });

        // result
        return [
            'header' => $pagination ?? [],
            'list' => $data ?? []
        ];
    }

    /**
     * 비밀번호 패턴 체크 함수
     *
     * @param string $pwd
     * @param string|null $email
     * @return bool
     * @throws QpickHttpException
     */
    static function chkCorrectPasswordPattern(string $pwd, string $email = null): bool
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

    /**
     * 비밀번호 일치여부 확인 함수
     *
     * @param $pwd
     * @return bool
     */
    static function chkPasswordMatched($pwd): bool
    {
        return hash::check($pwd, Auth::user()->password);
    }

    /**
     * @OA\Get(
     *      path="/v1/statistics/user/count-per-grade",
     *      summary="등급별 회원수 현황",
     *      description="등급별로 해당하는 회원의 건수를 확인",
     *      operationId="statisticsUserGrade",
     *      tags={"통계"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(type="object",
     *                  @OA\Property(property="grade", type="integer", example="1", description="회원등급"),
     *                  @OA\Property(property="cnt", type="integer", example="237", description="회원수")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated (비로그인)"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="forbidden (관리자가 아닌 상태에서 정보조회를 시도하는 상황 등)"
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
     *
     * 등급별 회원수 현황
     *
     * @param User $user
     * @return Collection
     */
    public function getStatUserByGrade(User $user): Collection
    {
        return Cache::tags('backoffice')->remember('user_count_per_grade', config('cache.custom.expire.common'), function () use ($user) {
            return $user->selectRaw('grade, count(id) as count')
                ->groupBy('grade')
                ->get();
        });
    }

    /**
     * @OA\Get(
     *      path="/v1/statistics/user/login-log/count-per-grade",
     *      summary="등급별 로그인횟수",
     *      description="입력한 기간 내에 로그인이 일어난 횟수를 각 회원등급별로 집계",
     *      operationId="statisticsLoginLogGrade",
     *      tags={"통계"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"startDate", "endDate"},
     *              @OA\Property(property="startDate", type="string", format="date-time", description="검색시작일", readOnly="true"),
     *              @OA\Property(property="endDate", type="string", format="date-time", description="검색종료일", readOnly="true"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(type="object",
     *                  @OA\Property(property="grade", type="integer", example="1", description="회원등급"),
     *                  @OA\Property(property="cnt", type="integer", example="237", description="회원수")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated (비로그인)"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="forbidden (관리자가 아닌 상태에서 정보조회를 시도하는 상황 등)"
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
     *
     * 기간내 등급별 로그인로그 통계
     *
     * @param LoginLogStatRequest $request
     * @return Collection
     */
    public function getCountLoginLogPerGrade(LoginLogStatRequest $request): Collection
    {
        return Cache::tags('backoffice')->remember('login_log_count_per_grade', config('cache.custom.expire.common'), function () use ($request) {
            $start = Carbon::parse($request->input('start_date'));
            $end = Carbon::parse($request->input('end_date'))->setTime(23, 59, 59);

            return ActionLog::loginLogStatistics($start, $end)
                ->get()
                ->makeHidden(['user']);
        });
    }

    /**
     * 회원 1명 쿼리 함수
     *
     * @param int $id
     * @return Builder|Builder[]|\Illuminate\Database\Eloquent\Collection|Model|null
     */
    protected function getOne(int $id)
    {
        $with = ['privacy', 'advAgree', 'sites', 'sites.userSolution'];

        if (Auth::hasAccessRightsToBackoffice()) {
            $with[] = 'backofficeLogs';
        }

        $user = $this->user->with($with)->findOrFail($id);

        if (Auth::hasAccessRightsToBackoffice()) {
            $user->makeVisible(['memo_for_managers']);
        }

        $user->name = $user->privacy->name;
        $user->email = $user->privacy->email;
        unset($user->privacy);

        return $user;
    }
}
