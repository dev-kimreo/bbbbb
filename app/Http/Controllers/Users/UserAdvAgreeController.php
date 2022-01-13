<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\SolutionRequest;
use App\Models\Users\UserAdvAgree;
use Illuminate\Http\JsonResponse;

class UserAdvAgreeController extends Controller
{
    public string $exceptionEntity = "userAdvertiseAgree";

    /**
     * @OA\Patch(
     *      path="/v1/user/{user_id}/adv-agree",
     *      summary="광고수신동의 수정",
     *      description="광고수신동의 수정",
     *      operationId="userAdvAgreeModify",
     *      tags={"회원관련"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"agree"},
     *              @OA\Property(property="agree", type="boolean", example=true, description="광고 수신동의 여부<br/>(1:동의, 0:미동의)"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="modified",
     *          @OA\JsonContent(ref="#/components/schemas/UserAdvAgree")
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
     * Update the specified resource in storage.
     *
     * @param int $user_id
     * @param SolutionRequest $req
     * @return JsonResponse
     */
    public function update(SolutionRequest $req, int $user_id): JsonResponse
    {
        // delete
        $userAdvAgree = UserAdvAgree::where('user_id', $user_id)->first();
        if ($userAdvAgree) {
            $userAdvAgree->delete();
        }

        // create
        return response()->json(UserAdvAgree::create([
            'user_id' => $user_id,
            'agree' => boolval($req->input('agree'))
        ]), 201);
    }
}
