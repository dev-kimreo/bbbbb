<?php

namespace App\Http\Controllers;

use App\Events\Member\Login as LoginEvent;
use App\Events\Member\Logout as LogoutEvent;
use App\Exceptions\QpickHttpException;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use Hash;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Http\Controllers\AccessTokenController as ATC;
use Psr\Http\Message\ServerRequestInterface;
use Validator;

class AccessTokenController extends ATC
{
    protected User $user;

    /**
     * @throws QpickHttpException
     * @throws ValidationException
     */
    public function store(Request $req, ServerRequestInterface $request, User $user): Response
    {
        $this->user = $user;
        $this->req = $req;
        return $this->issueToken($request);
    }

    /**
     * @OA\Post(
     *      path="/v1/user/auth",
     *      summary="로그인",
     *      description="회원 로그인",
     *      operationId="memberLogin",
     *      tags={"회원관련"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"grantType","clientId", "clientSecret", "username", "password"},
     *              @OA\Property(property="grantType", type="string", example="password"),
     *              @OA\Property(property="clientId", type="integer", example="1", description="client id"),
     *              @OA\Property(property="clientSecret", type="string", example="W6dubYlWANIy85Wdv5b4jx21NW43m5VC2yHB8Oy0", description="client secret key"),
     *              @OA\Property(property="username", type="string", example="abcd@davinci.com", description="이메일"),
     *              @OA\Property(property="password", type="string", format="password", example="1234qwer", description="비밀번호"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="로그인되었습니다.",
     *          @OA\JsonContent(
     *              @OA\Property(property="tokenType", type="string", example="Bearer"),
     *              @OA\Property(property="expiresIn", type="integer", example=31536000),
     *              @OA\Property(property="accessToken", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiZGY5YTI3OGE4YzRiZGM3YmM1NmQwZGFhNjQxYzNjYmRjYjEzM2ZkZGFkMWMxNzQ1YWU3ZDZiODM2NTI4ZDUwM2U0NjMyYWJhYjA2NWIxMTAiLCJpYXQiOiIxNjE2NTczODA0LjI2MTU2MyIsIm5iZiI6IjE2MTY1NzM4MDQuMjYxNTY3IiwiZXhwIjoiMTY0ODEwOTgwNC4yNDMxOTYiLCJzdWIiOiIxIiwic2NvcGVzIjpbXX0.WqgNN-8mX6hHehrkN77rGzzsDZOy-USzfyzqnuVnJLTSpTNlVK3FM0OpzGUnYOFwP2rCOoibAOcJX7xue2QeYtwu6QFWAPZIeJAi780ECPTdxcbTzAcWC9ckCQ0ryVKDk0cex2WAOvI3pOPFiKWvciAnqdKY7yvjcjFIxbvyZ5i-d0KoKZa6ucRjGU3msyky1pWwje1sYnkUE77kk8480TbnLPoHVe7PjRKwfsdUBVrYJPmdxJd-mh-OLL9c1UNHTqIPsn1PSpD-SdAxOfNwYrc8g-D1KBtsXv_GhO3L1L0lL7-jp_Ocmk_uFY8Z4Z89-7ZCNCrqHx4W1K2keNB8P8o7qI89BPWLBxDSYXJ8Pm0y6ajN_gvQRHPD9OzVPlpc212YwgWnt9ErbGeGK2cC1cyAZOikC84ye2jHGXs3dbozUrkBSkjWl8O-kU65uk3M7kiaB6BpIhE1sCbLOC55uCJSQInsInKQNUAvxlZNHSLeWwxaUP-kt-owYW9ResWNs10ofPkSIC31DFpx77eo98SeX4g5s69dDCVr1wvo_9lg1D8QOUvALNAR_ghN-O6ChvSWmxTvfVsiXIRaj413rLtSu1HgTSuBM0b-3DsjZrDEbHDYGnKNany0x-I3NXjUelKQwGb6JEixGmcnO5Yj7x5dCzCYVSd_EfeuHDxfhnk"),
     *              @OA\Property(property="refreshToken", type="string", example="def50200907238e0176797c91a5fd0519bc797427bef19138d1c8e242829756cd688c89392fc690c4236195dc9f967fc202c9c996919b661ffe3f04dffeecf5b61cfcd41efadd5071b278ec09dea33b669f1a7efab09b1d68a66239ebd661769e6edc8b12f537e8de5ec8753dc2e7a1a46e62796c40375a8ae105e810b3b62a480cfe1512a2d4c2853e3a365da0bad60fba4ae0b9d9a17b49f28232bb8d63633f37f9a2de04287a2078a147c78d6ae81def17f96720759bca3387964391232c27fdec822e02fb25aff15709950e5f56c67e57dda854b06b75967760455adbca2c19cbd9313d64aff95bdcdeabb14220012d3c5d3636ee0330ed44f993004ab1558fd196feaf2c41ef4537dd1fc35eb31c0c99c2271d565d6c162759c78a65ae1a3b33912e74a1908f2fbe98b78ff83fb64f4f1699618d974db09d5330b2e49c3816c3f8295b3c1303bcae85080d33038e30d4c62804b5ab5124902c097cce7c8f0"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="The authorization grant type is not supported by the authorization server."
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Client authentication failed"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed login"
     *      )
     *  )
     * @param ServerRequestInterface $request
     * @return Response
     * @throws QpickHttpException
     * @throws ValidationException
     */
    public function issueToken(ServerRequestInterface $request): Response
    {
        // Getting the body of request
        $requestBody = $request->getParsedBody();

        // Validation
        Validator::make($requestBody, [
            'username' => 'required|email',
            'password' => 'required|string|min:8',
            'grant_type' => 'required',
            'client_id' => 'required|integer',
            'client_secret' => 'required|string',
        ])->validate();

        // Check Email
        $username = $requestBody['username'];
        $password = $requestBody['password'];

        $user = $this->user::status('active')->whereHas('privacy', function (Builder $q) use ($username) {
            $q->where('email', $username);
        })->first();

        $checkInactive = $this->user::status('inactive')->whereHas('privacy', function (Builder $q) use ($username) {
            $q->where('email', $username);
        })->first();

        // Backoffice 로그인 권한 체크
        if ($requestBody['client_id'] == '2') {
            if (!$user->manager) {
                throw new QpickHttpException(403, 'common.unauthorized');
            }
        } else if ($requestBody['client_id'] == '3') {  // Partner center 로그인 권한 체크
            if (!$user->partner) {
                throw new QpickHttpException(403, 'common.forbidden');
            }
        }

        if ($checkInactive) {
            throw new QpickHttpException(403, 'user.inactive');
        }

        if (!$user) {
            throw new QpickHttpException(404, 'user.username.incorrect');
        }

        if (!hash::check($password, $user->password)) {
            throw new QpickHttpException(422, 'user.password.incorrect');
        }

        LoginEvent::dispatch($this->req, $user->id, $user->grade, $requestBody['client_id']);

        // 로그인 시간 남기기
        $user->update(['last_authorized_at' => Carbon::now()]);

        return parent::issueToken($request);
    }


    /**
     * @OA\Get(
     *      path="/v1/user/auth",
     *      summary="로그인된 회원정보",
     *      description="로그인된 회원정보 열람",
     *      operationId="userAuthInfo",
     *      tags={"회원관련"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/User")
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     */
    public function show(): Collection
    {
        return collect(Auth::user());
    }


    /**
     * @OA\Delete(
     *      path="/v1/user/auth",
     *      summary="로그아웃",
     *      description="회원 로그아웃",
     *      operationId="userLogout",
     *      tags={"회원관련"},
     *      @OA\Response(
     *          response=204,
     *          description="successfully"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     *
     * 로그아웃
     *
     * @param Request $req
     * @return Response
     */
    public function destroy(Request $req): Response
    {
        // Init
        $user = Auth::user();

        // 엑세스 토큰 제거
        $user->token()->revoke();

        // refresh token revoke
//        $refreshTokenRepository = app('Laravel\Passport\RefreshTokenRepository');
//        $refreshTokenRepository->revokeRefreshTokensByAccessTokenId(auth()->user()->token()->id);

        // Dispatch the logout event
        LogoutEvent::dispatch($req, $user->id, $user->grade, Auth::getClientId());

        return response()->noContent();
    }

}
