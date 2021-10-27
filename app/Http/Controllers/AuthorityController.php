<?php

namespace App\Http\Controllers;

use App\Exceptions\QpickHttpException;
use App\Libraries\PaginationLibrary;
use App\Models\Authority;
use App\Http\Requests\Members\Authorities\StoreAuthorityRequest;
use App\Http\Requests\Members\Authorities\UpdateAuthorityRequest;
use App\Models\BackofficeMenu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

use function PHPUnit\Framework\isInstanceOf;

class AuthorityController extends Controller
{
    /**
     * @OA\Get(
     *      path="/v1/authority",
     *      summary="관리자그룹 목록",
     *      description="관리자그룹의 전체목록을 표시합니다",
     *      operationId="authorityGetList",
     *      tags={"관리자"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={}
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/Pagination" ),
     *              @OA\Property(property="list", type="array",
     *                  @OA\Items(
     *                      allOf={
     *                          @OA\Schema(ref="#/components/schemas/Authority"),
     *                          @OA\Schema(
     *                              @OA\Property(property="managersCount", type="integer", example=3, description="그룹 인원" )
     *                          )
     *                      }
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      )
     *  )
     */

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return array
     */
    public function index(Request $request): array
    {
        $data = Authority::withCount('managers')->get();


        return [
            'header' => PaginationLibrary::set($request->input('page'), $data->count(), $request->input('per_page')),
            'list' => $data
        ];
    }

    /**
     * @OA\Post (
     *      path="/v1/authority",
     *      summary="관리자그룹 등록",
     *      description="새로운 관리자그룹을 등록합니다",
     *      operationId="authorityCreate",
     *      tags={"관리자"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"code", "title", "display_name"},
     *              @OA\Property(property="code", type="string", example="120", description="그룹번호" ),
     *              @OA\Property(property="title", type="string", example="시스템관리자", description="그룹명" ),
     *              @OA\Property(property="displayName", type="string", example="운영자", description="닉네임" ),
     *              @OA\Property(property="memo", type="string", example="큐픽 사이트 운영", description="설명" )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Authority")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      )
     *  )
     */

    /**
     * Store a newly created resource in storage.
     *
     * @param Request|StoreAuthorityRequest $request
     * @return Collection
     */
    public function store(StoreAuthorityRequest $request): Collection
    {
        // store
        $authority = Authority::create($request->all());

        return collect(Authority::find($authority->id));
    }

    /**
     * @OA\Get(
     *      path="/v1/authority/{id}",
     *      summary="관리자그룹 상세",
     *      description="관리자그룹 1개의 상세정보를 표시합니다",
     *      operationId="authorityGetInfo",
     *      tags={"관리자"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={}
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Authority")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      )
     *  )
     */

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Collection
     */
    public function show(int $id): Collection
    {
        return collect(Authority::findOrFail($id));
    }

    /**
     * @OA\Patch (
     *      path="/v1/authority/{id}",
     *      summary="관리자그룹 수정",
     *      description="기존에 등록된 관리자그룹을 수정합니다",
     *      operationId="authorityModify",
     *      tags={"관리자"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={},
     *              @OA\Property(property="code", type="string", example="13", description="그룹번호" ),
     *              @OA\Property(property="title", type="string", example="시스템관리자", description="그룹명" ),
     *              @OA\Property(property="displayName", type="string", example="운영자", description="닉네임" ),
     *              @OA\Property(property="memo", type="string", example="큐픽 사이트 운영", description="설명" )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Authority")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      )
     *  )
     */

    /**
     * Update the specified resource in storage.
     *
     * @param Request|UpdateAuthorityRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateAuthorityRequest $request, int $id): JsonResponse
    {
        // getting original data & update
        Authority::findOrFail($id)->update($request->all());

        // response
        return response()->json(collect(Authority::find($id)), 201);
    }

    /**
     * @OA\Delete(
     *      path="/v1/authority/{id}",
     *      summary="관리자그룹 삭제",
     *      description="기존에 등록된 관리자그룹을 삭제합니다",
     *      operationId="authorityDelete",
     *      tags={"관리자"},
     *      @OA\RequestBody(
     *          required=true,
     *          description=""
     *      ),
     *      @OA\Response(
     *          response=204,
     *          description="successfully"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="unauthenticated"
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     */

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     * @throws QpickHttpException
     */
    public function destroy(int $id): Response
    {
        $authority = Authority::withCount('managers')->findOrFail($id);

        if ($authority->managers_count > 0) {
            throw new QpickHttpException(422, 'authority.delete.disable.exists_manager');
        }

        Authority::destroy($id);
        return response()->noContent();
    }



    /**
     * @OA\Get (
     *      path="/v1/authority/{id}/menu-permission",
     *      summary="관리자그룹 메뉴권한목록",
     *      description="관리자 그룹의 메뉴 권한 목록",
     *      operationId="indexAuthorityMenuPermission",
     *      tags={"관리자"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(type="array",
     *              @OA\Items(
     *                  allOf={
     *                      @OA\Schema(ref="#/components/schemas/BackofficeMenu"),
     *                      @OA\Schema(
     *                          @OA\Property(property="permission", type="object", ref="#/components/schemas/BackofficePermission")
     *                      )
     *                  }
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      )
     *  )
     */

    public function getMenuListWithPermission($authority_id)
    {
        $authCollect = Authority::findOrFail($authority_id);
        $usablePermissions = $authCollect->permissions->keyBy('backoffice_menu_id');

        $menuModel = BackofficeMenu::where(['depth' => 1]);
        $menuModel->with('children.children');
        $menuModel = $menuModel->get();

        array_walk_recursive($menuModel, [$this, 'addPermissionAtMenu'], $usablePermissions);

        return $menuModel;
    }


    public function addPermissionAtMenu($item, $key, $permissions)
    {
        if (!$item) {
            return;
        }

        if (!$item->last && count($item->children)) {
            array_walk_recursive($item->children, [$this, 'addPermissionAtMenu'], $permissions);
        }

        $item->permission = $permissions->get($item->id);
    }

}
