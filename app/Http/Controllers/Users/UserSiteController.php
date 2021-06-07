<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\SiteRequest;
use App\Models\UserSite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserSiteController extends Controller
{
    /**
     * @OA\Post(
     *      path="/v1/user/{user_id}/site",
     *      summary="사이트 정보 추가",
     *      description="새로운 사이트 정보 추가",
     *      operationId="userSiteCreate",
     *      tags={"회원관련"},
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/UserSite")
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
     * @param Request $req
     * @param int $user_id
     * @return JsonResponse
     */
    public function store(SiteRequest $req, int $user_id): JsonResponse
    {
        $params = array_merge($req->toArray(), ['user_id' => $user_id]);
        $data = UserSite::create($params);
        return response()->json(collect($this->getOne($data->id)), 201);
    }

    /**
     * @OA\Patch(
     *      path="/v1/user/{user_id}/site",
     *      summary="사이트 정보 수정",
     *      description="새로운 사이트 정보 수정",
     *      operationId="userSiteUpdate",
     *      tags={"회원관련"},
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/UserSite")
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
     * @param SiteRequest $req
     * @param int $user_id
     * @param int $id
     * @return JsonResponse
     */
    public function update(SiteRequest $req, int $user_id, int $id): JsonResponse
    {
        UserSite::findOrfail($id)->update($req->toArray());
        return response()->json(collect($this->getOne($id)), 201);
    }

    /**
     * @OA\delete(
     *      path="/v1/user/{user_id}/site",
     *      summary="사이트 정보 삭제",
     *      description="기존 사이트 정보 삭제",
     *      operationId="userSiteDelete",
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
        UserSite::findOrFail($solution_id)->delete();
        return response()->noContent();
    }

    protected function getOne($id)
    {
        return UserSite::find($id);
    }
}
