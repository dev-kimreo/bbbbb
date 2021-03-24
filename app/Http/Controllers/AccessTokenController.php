<?php

namespace App\Http\Controllers;

use App\Models\User;

use Hash;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ServerRequestInterface;
use Response;
use Validator;
use \Laravel\Passport\Http\Controllers\AccessTokenController as ATC;


class AccessTokenController extends ATC {

    /**
     * @OA\Post(
     *      path="/v1/member/auth",
     *      summary="로그인",
     *      description="회원 로그인",
     *      operationId="memberLogin",
     *      tags={"Members"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"grant_type","client_id", "client_secret", "username", "password"},
     *              @OA\Property(property="grant_type", type="string", example="password"),
     *              @OA\Property(property="client_id", type="integer", example="1", description="client id"),
     *              @OA\Property(property="client_secret", type="string", example="W6dubYlWANIy85Wdv5b4jx21NW43m5VC2yHB8Oy0", description="client secret key"),
     *              @OA\Property(property="username", type="string", example="abcd@davinci.com", description="이메일"),
     *              @OA\Property(property="password", type="string", format="password", example="1234qwer", description="비밀번호"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="로그인되었습니다.",
     *          @OA\JsonContent(
     *              @OA\Property(property="token_type", type="string", example="Bearer"),
     *              @OA\Property(property="expires_in", type="integer", example=31536000),
     *              @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiZGY5YTI3OGE4YzRiZGM3YmM1NmQwZGFhNjQxYzNjYmRjYjEzM2ZkZGFkMWMxNzQ1YWU3ZDZiODM2NTI4ZDUwM2U0NjMyYWJhYjA2NWIxMTAiLCJpYXQiOiIxNjE2NTczODA0LjI2MTU2MyIsIm5iZiI6IjE2MTY1NzM4MDQuMjYxNTY3IiwiZXhwIjoiMTY0ODEwOTgwNC4yNDMxOTYiLCJzdWIiOiIxIiwic2NvcGVzIjpbXX0.WqgNN-8mX6hHehrkN77rGzzsDZOy-USzfyzqnuVnJLTSpTNlVK3FM0OpzGUnYOFwP2rCOoibAOcJX7xue2QeYtwu6QFWAPZIeJAi780ECPTdxcbTzAcWC9ckCQ0ryVKDk0cex2WAOvI3pOPFiKWvciAnqdKY7yvjcjFIxbvyZ5i-d0KoKZa6ucRjGU3msyky1pWwje1sYnkUE77kk8480TbnLPoHVe7PjRKwfsdUBVrYJPmdxJd-mh-OLL9c1UNHTqIPsn1PSpD-SdAxOfNwYrc8g-D1KBtsXv_GhO3L1L0lL7-jp_Ocmk_uFY8Z4Z89-7ZCNCrqHx4W1K2keNB8P8o7qI89BPWLBxDSYXJ8Pm0y6ajN_gvQRHPD9OzVPlpc212YwgWnt9ErbGeGK2cC1cyAZOikC84ye2jHGXs3dbozUrkBSkjWl8O-kU65uk3M7kiaB6BpIhE1sCbLOC55uCJSQInsInKQNUAvxlZNHSLeWwxaUP-kt-owYW9ResWNs10ofPkSIC31DFpx77eo98SeX4g5s69dDCVr1wvo_9lg1D8QOUvALNAR_ghN-O6ChvSWmxTvfVsiXIRaj413rLtSu1HgTSuBM0b-3DsjZrDEbHDYGnKNany0x-I3NXjUelKQwGb6JEixGmcnO5Yj7x5dCzCYVSd_EfeuHDxfhnk"),
     *              @OA\Property(property="refresh_token", type="string", example="def50200907238e0176797c91a5fd0519bc797427bef19138d1c8e242829756cd688c89392fc690c4236195dc9f967fc202c9c996919b661ffe3f04dffeecf5b61cfcd41efadd5071b278ec09dea33b669f1a7efab09b1d68a66239ebd661769e6edc8b12f537e8de5ec8753dc2e7a1a46e62796c40375a8ae105e810b3b62a480cfe1512a2d4c2853e3a365da0bad60fba4ae0b9d9a17b49f28232bb8d63633f37f9a2de04287a2078a147c78d6ae81def17f96720759bca3387964391232c27fdec822e02fb25aff15709950e5f56c67e57dda854b06b75967760455adbca2c19cbd9313d64aff95bdcdeabb14220012d3c5d3636ee0330ed44f993004ab1558fd196feaf2c41ef4537dd1fc35eb31c0c99c2271d565d6c162759c78a65ae1a3b33912e74a1908f2fbe98b78ff83fb64f4f1699618d974db09d5330b2e49c3816c3f8295b3c1303bcae85080d33038e30d4c62804b5ab5124902c097cce7c8f0"),
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
     *                          property="10010",
     *                          type="object",
     *                          description="필수 파라미터를 확인해주세요.",
     *                          @OA\Property(
     *                              property="key",
     *                              type="string",
     *                              example="grant_type",
     *                          ),
     *                          @OA\Property(
     *                              property="message",
     *                              type="string",
     *                          ),
     *                      ),
     *                      @OA\Property(
     *                          property="10301",
     *                          type="object",
     *                          description="존재하지 않는 아이디(이메일) 입니다.",
     *                          @OA\Property(
     *                              property="message",
     *                              type="string",
     *                          ),
     *                      ),
     *                      @OA\Property(
     *                          property="10311",
     *                          type="object",
     *                          description="로그인 정보가 올바르지 않습니다.",
     *                          @OA\Property(
     *                              property="message",
     *                              type="string",
     *                          ),
     *                      ),
     *                      @OA\Property(
     *                          property="10401",
     *                          type="object",
     *                          description="잘못된 인증 방식입니다.",
     *                          @OA\Property(
     *                              property="message",
     *                              type="string",
     *                          ),
     *                      ),
     *                  )
     *              )
     *          )
     *      )
     *  )
     */
    public function issueToken(ServerRequestInterface $request) {

        try {
            $mess = [
                'username.required' => getErrorCode(10010),
                'password.required' => getErrorCode(10010),
                'grant_type.required' => getErrorCode(10010),
                'client_id.required' => getErrorCode(10010),
                'client_secret.required' => getErrorCode(10010),
            ];

            $validator = Validator::make($request->getParsedBody(), [
                'username' => 'required|email',
                'password' => 'required|string|min:8',
                'grant_type' => 'required',
                'client_id' => 'required|integer',
                'client_secret' => 'required|string',
            ], $mess);


            $resErr = getValidationErrToArr($validator->errors());

            if ($validator->fails()) {
                return response()->json($resErr, 422);
            }

            $username = $request->getParsedBody()['username'];
            $password = $request->getParsedBody()['password'];

            $member = User::where('email', $username)->first();

            if( !$member ) {
                return response()->json(getResponseError(10301), 422);
            }

            if ( !hash::check($password, $member->password) ) {
                return response()->json(getResponseError(10311), 422);
            }

            $tokenResponse = parent::issueToken($request);
            //convert response to json string
            $content = $tokenResponse->getContent();

            //convert json to array
            $data = json_decode($content, true);

            return response()->json($data, 200);
        }
        catch (Exception $e) {
            ////return error message
            return response()->json(getResponseError(10401), 422);
        }


    }

}
