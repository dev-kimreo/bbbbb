<?php

namespace App\Http\Controllers\Users;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UserSites\DestroyRequest;
use App\Http\Requests\Users\UserSites\IndexRequest;
use App\Http\Requests\Users\UserSites\StoreRequest;
use App\Http\Requests\Users\UserSites\UpdateRequest;
use App\Libraries\PaginationLibrary;
use App\Models\Users\UserSite;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class UserSiteController extends Controller
{
    /**
     * @OA\Get(
     *      path="/v1/user/{user_id}/site",
     *      summary="사이트 정보 목록",
     *      description="사이트 정보 목록",
     *      operationId="UserSiteList",
     *      tags={"회원관련"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="page", type="integer", example=1, default=1, description="페이지"),
     *              @OA\Property(property="perPage", type="integer", example=15, default=15, description="한 페이지당 보여질 갯 수"),
     *              @OA\Property(property="userId", type="integer", example="372", description="회원 고유번호 (백오피스 로그인 시에만 사용가능)"),
     *              @OA\Property(property="isSetSolution", type="boolean", example="1", description="솔루션 연동정보 연결여부<br />0: 미연결, 1:연결")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/Pagination"),
     *              @OA\Property(property="list", type="array",
     *                  @OA\Items(type="object", ref="#/components/schemas/UserSite")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "davinci_auth":{},
     *          "admin_auth":{}
     *      }}
     *  )
     *
     * @param IndexRequest $req
     * @param int $user_id
     * @return Collection
     */
    public function index(IndexRequest $req, int $user_id): Collection
    {
        // init model
        $model = UserSite::query()
            ->with('userSolution.solution')
            ->orderByDesc('id');

        // query
        $model->where('user_id', $user_id);

        if ($v = $req->input('solution_id')) {
            $model->whereHas('userSolution', function ($query) use ($v) {
                return $query->where('solution_id', $v);
            });
        }

        if ($v = $req->input('user_solution_id')) {
            $model->where('user_solution_id', $v);
        }

        if ($req->exists('is_set_solution')) {
            if($req->input('is_set_solution')) {
                $model->whereNotNull('user_solution_id');
            } else {
                $model->whereNull('user_solution_id');
            }
        }

        // set pagination information
        $pagination = PaginationLibrary::set($req->input('page'), $model->count(), $req->input('per_page'));

        // get data
        $data = $model->skip($pagination['skip'])->take($pagination['perPage'])->get();

        // result
        $result = [
            'header' => $pagination ?? [],
            'list' => $data ?? []
        ];

        return collect($result);
    }

    /**
     * @OA\Post(
     *      path="/v1/user/{user_id}/site",
     *      summary="사이트 정보 추가",
     *      description="사이트 정보 추가",
     *      operationId="userSiteCreate",
     *      tags={"회원관련"},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="userSolutionId", type="integer", example=3, description="솔루션 연동정보 고유번호" ),
     *              @OA\Property(property="name", type="string", maxLength=32, description="사이트명", example="J맨즈 컬렉션"),
     *              @OA\Property(property="url", type="string", maxLength=256, description="사이트 URL", example="https://jmans.co.kr"),
     *              @OA\Property(property="biz_type", type="string", maxLength=16, description="쇼핑몰 분류", example="남성의류"),
     *          ),
     *      ),
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
     * @param StoreRequest $req
     * @param int $user_id
     * @return JsonResponse
     */
    public function store(StoreRequest $req, int $user_id): JsonResponse
    {
        $params = array_merge($req->toArray(), ['user_id' => $user_id]);
        $data = UserSite::query()->create($params);
        return response()->json(collect($this->getOne($data->id)), 201);
    }

    /**
     * @OA\Get(
     *      path="/v1/user/{user_id}/site/{site_id}",
     *      summary="사이트 정보 상세",
     *      description="사이트 정보 상세",
     *      operationId="userSiteInfo",
     *      tags={"회원관련"},
     *      @OA\Response(
     *          response=200,
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
     * Display the specified resource.
     *
     * @param int $user_id
     * @param int $site_id
     * @return Collection
     */
    public function show(int $user_id, int $site_id): Collection
    {
        return collect($this->getOne($site_id));
    }

    /**
     * @OA\Patch(
     *      path="/v1/user/{user_id}/site/{site_id}",
     *      summary="사이트 정보 수정",
     *      description="사이트 정보 수정",
     *      operationId="userSiteUpdate",
     *      tags={"회원관련"},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="userSolutionId", type="integer", example=3, description="솔루션 연동정보 고유번호" ),
     *              @OA\Property(property="name", type="string", maxLength=32, description="사이트명", example="J맨즈 컬렉션"),
     *              @OA\Property(property="url", type="string", maxLength=256, description="사이트 URL", example="https://jmans.co.kr"),
     *              @OA\Property(property="biz_type", type="string", maxLength=16, description="쇼핑몰 분류", example="남성의류"),
     *          ),
     *      ),
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
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $req
     * @param int $user_id
     * @param int $site_id
     * @return JsonResponse
     */
    public function update(UpdateRequest $req, int $user_id, int $site_id): JsonResponse
    {
        UserSite::query()->findOrfail($site_id)->update($req->toArray());
        return response()->json(collect($this->getOne($site_id)), 201);
    }

    /**
     * @OA\delete(
     *      path="/v1/user/{user_id}/site/{site_id}",
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
     * Remove the specified resource from storage.
     *
     * @param DestroyRequest $req
     * @param int $user_id
     * @param int $site_id
     * @return Response
     */
    public function destroy(DestroyRequest $req, int $user_id, int $site_id): Response
    {
        UserSite::query()->findOrFail($site_id)->delete();
        return response()->noContent();
    }


    protected function getOne($id): Collection
    {
        return collect(UserSite::query()->with('userSolution.solution')->find($id));
    }
}
