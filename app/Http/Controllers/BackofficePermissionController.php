<?php

namespace App\Http\Controllers;

use App\Exceptions\QpickHttpException;
use App\Http\Requests\BackofficeMenus\Permissions\StoreRequest;
use App\Models\BackofficeMenu;
use App\Models\BackofficePermission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;


class BackofficePermissionController extends Controller
{
    private BackofficePermission $permission;

    public function __construct(BackofficePermission $permission)
    {
        $this->permission = $permission;
    }

    /**
     * @OA\Post (
     *      path="/v1/backoffice-permission",
     *      summary="메뉴 권한 등록",
     *      description="새로운 메뉴 권한을 등록합니다.",
     *      operationId="menuPermissionCreate",
     *      tags={"메뉴 권한"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"name"},
     *              @OA\Property(property="authorityId", ref="#/components/schemas/BackofficePermission/properties/authorityId"),
     *              @OA\Property(property="backofficeMenuId", ref="#/components/schemas/BackofficePermission/properties/backofficeMenuId"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/BackofficePermission")
     *      ),
     *      @OA\Response(
     *          response=409,
     *          description="duplicate Error"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      )
     *  )
     * @param StoreRequest $req
     * @return JsonResponse
     * @throws QpickHttpException
     */
    public function store(StoreRequest $req): JsonResponse
    {
        $menuCollect = BackofficeMenu::find($req->input('backoffice_menu_id'));
        if (!$menuCollect->last) {
            throw new QpickHttpException(422, 'menu.permission.only.last');
        }

        $this->permission = $this->permission->firstOrCreate(
            $req->all()
        );
        $this->permission->refresh();

        return response()->json($this->permission, 201);
    }


    /**
     * @OA\Get (
     *      path="/v1/backoffice-permission/{permission_id}",
     *      summary="메뉴 권한 상세",
     *      description="메뉴 권한의 상세정보",
     *      operationId="menuPermissionShow",
     *      tags={"메뉴 권한"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/BackofficePermission")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      )
     *  )
     * @param Request $req
     * @param $permission_id
     * @return BackofficePermission
     */
    public function show(Request $req, $permission_id): BackofficePermission
    {
        return $this->permission->findOrfail($permission_id);
    }


    /**
     * @OA\Get (
     *      path="/v1/backoffice-permission",
     *      summary="메뉴 권한 목록",
     *      description="메뉴 권한의 목록",
     *      operationId="menuPermissionIndex",
     *      tags={"메뉴 권한"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/BackofficePermission")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      )
     *  )
     * @param Request $req
     * @return Collection
     */
    public function index(Request $req): Collection
    {
        return $this->permission->all();
    }


    /**
     * @OA\Delete (
     *      path="/v1/backoffice-permission/{permission_id}",
     *      summary="메뉴 권한 삭제",
     *      description="메뉴 권한을 삭제합니다",
     *      operationId="menuPermissionDestroy",
     *      tags={"메뉴 권한"},
     *      @OA\Response(
     *          response=204,
     *          description="successfully",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      )
     *  )
     * @param Request $req
     * @param $permission_id
     * @return Response
     *
     */
    public function destroy(Request $req, $permission_id): Response
    {
        $permission = $this->permission->find($permission_id);

        if ($permission) {
            $permission->delete();
        }

        return response()->noContent();
    }
}
