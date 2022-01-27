<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\SolutionRequest;
use App\Models\Users\UserSolution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class UserSolutionController extends Controller
{
    public string $exceptionEntity = "userSolution";

    ## TODO 추후 백오피스에 기획에 따라 백오피스 로그 남겨야 함.

    /**
     * @OA\Post(
     *      path="/v1/user/{user_id}/solution",
     *      summary="솔루션 연동정보 추가",
     *      description="새로운 솔루션 연동정보 추가",
     *      operationId="userSolutionCreate",
     *      tags={"회원관련"},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="solutionId", type="integer", example=3, description="솔루션 고유번호" ),
     *              @OA\Property(property="solutionUserId", type="string", maxLength=128, description="연동된 솔루션 회원 ID", example="honggildong"),
     *              @OA\Property(property="apikey", type="string", maxLength=512, description="연동된 솔루션의 API Key", example="apikey31f7sdg6bsd73")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/UserSolution")
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Client authentication failed"
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
     * @param SolutionRequest $req
     * @param int $user_id
     * @return JsonResponse
     */
    public function store(SolutionRequest $req, int $user_id): JsonResponse
    {
        $params = array_merge($req->toArray(), ['user_id' => $user_id]);
        $data = UserSolution::create($params);
        return response()->json(collect($this->getOne($data->id)), 201);
    }

    /**
     * @OA\Patch(
     *      path="/v1/user/{user_id}/solution/{solution_id}",
     *      summary="솔루션 연동정보 수정",
     *      description="새로운 솔루션 연동정보 수정",
     *      operationId="userSolutionUpdate",
     *      tags={"회원관련"},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="solutionId", type="integer", example=3, description="솔루션 고유번호" ),
     *              @OA\Property(property="solutionUserId", type="string", maxLength=128, description="연동된 솔루션 회원 ID", example="honggildong"),
     *              @OA\Property(property="apikey", type="string", maxLength=512, description="연동된 솔루션의 API Key", example="apikey31f7sdg6bsd73")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/UserSolution")
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Client authentication failed"
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
     * @param SolutionRequest $req
     * @param int $user_id
     * @param int $id
     * @return JsonResponse
     */
    public function update(SolutionRequest $req, int $user_id, int $id): JsonResponse
    {
        UserSolution::findOrfail($id)->update($req->toArray());
        return response()->json(collect($this->getOne($id)), 201);
    }

    /**
     * @OA\delete(
     *      path="/v1/user/{user_id}/solution/{solution_id}",
     *      summary="솔루션 연동정보 삭제",
     *      description="기존 솔루션 연동정보 삭제",
     *      operationId="userSolutionDelete",
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
     * @param int $user_id
     * @param int $solution_id
     * @return Response
     */
    public function destroy(int $user_id, int $solution_id): Response
    {
        UserSolution::findOrFail($solution_id)->delete();
        return response()->noContent();
    }

    protected function getOne($id)
    {
        return UserSolution::find($id);
    }
}
