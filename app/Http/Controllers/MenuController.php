<?php

namespace App\Http\Controllers;

use App\Exceptions\QpickHttpException;
use App\Http\Requests\Menus\StoreRequest;
use App\Http\Requests\Menus\UpdateRequest;
use App\Http\Requests\Menus\IndexRequest;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class MenuController extends Controller
{
    private Menu $menu;

    public function __construct(Menu $menu)
    {
        $this->menu = $menu;
    }


    /**
     * @OA\Post (
     *      path="/v1/menu",
     *      summary="메뉴 등록",
     *      description="새로운 메뉴를 등록합니다.",
     *      operationId="menuCreate",
     *      tags={"메뉴"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"name"},
     *              @OA\Property(property="name", ref="#/components/schemas/Menu/properties/name"),
     *              @OA\Property(property="parent", ref="#/components/schemas/Menu/properties/parent"),
     *              @OA\Property(property="sort", ref="#/components/schemas/Menu/properties/sort")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Menu")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      )
     *  )
     * @param StoreRequest $req
     * @return Menu
     */
    public function store(StoreRequest $req): Menu
    {
        $mergeArrays = [];

        // 특정 메뉴 하위로 추가할 경우
        if ($s = $req->input('parent')) {
            $parentModel = $this->menu->findOrFail($s);
            $parentModel->last = 0;
            $parentModel->save();

            $mergeArrays = [
                'depth' => $parentModel->depth + 1
            ];
        }

        $this->menu = $this->menu::create(array_merge(
            $req->all(),
            $mergeArrays
        ));

        $this->menu->refresh();

        return $this->menu;
    }


    /**
     * @OA\Patch (
     *      path="/v1/menu/{menu_id}",
     *      summary="메뉴 수정",
     *      description="메뉴를 수정합니다.",
     *      operationId="menuUpdate",
     *      tags={"메뉴"},
     *      @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="name", ref="#/components/schemas/Menu/properties/name"),
     *              @OA\Property(property="sort", ref="#/components/schemas/Menu/properties/sort")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Menu")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      )
     *  )
     * @param UpdateRequest $req
     * @param $menu_id
     * @return Menu
     */
    public function update(UpdateRequest $req, $menu_id): Menu
    {
        $this->menu = $this->menu->findOrFail($menu_id);
        $this->menu->fill($req->all());
        $this->menu->save();

        return $this->menu;
    }


    /**
     * @OA\Get (
     *      path="/v1/menu/{menu_id}",
     *      summary="메뉴 상세",
     *      description="메뉴의 상세정보",
     *      operationId="menuShow",
     *      tags={"메뉴"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Menu")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      )
     *  )
     * @param Request $req
     * @param $menu_id
     * @return Menu
     */
    public function show(Request $req, $menu_id): Menu
    {
        $this->menu = $this->menu->findOrFail($menu_id);

        return $this->menu;
    }

    /**
     * @OA\Get (
     *      path="/v1/menu",
     *      summary="메뉴 목록",
     *      description="메뉴의 목록",
     *      operationId="menuIndex",
     *      tags={"메뉴"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Menu")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      )
     *  )
     * @param IndexRequest $req
     * @return Collection
     */
    public function index(IndexRequest $req): Collection
    {
        $menuModel = $this->menu->where(['depth' => 1]);
        $menuModel->with('children.children');
        $menuModel = $menuModel->get();

        return collect($menuModel);
    }


    /**
     * @OA\Delete (
     *      path="/v1/menu/{menu_id}",
     *      summary="메뉴 삭제",
     *      description="메뉴를 삭제합니다",
     *      operationId="menuDestroy",
     *      tags={"메뉴"},
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
     * @param $menu_id
     * @return Response
     * @throws QpickHttpException
     */
    public function destroy(Request $req, $menu_id): Response
    {
        $this->menu = $this->menu->withCount('children')->findOrFail($menu_id);

        if ($this->menu->children_count) {
            throw new QpickHttpException(422, 'menu.delete.disable.exists_children');
        }

        $this->menu->delete();

        return response()->noContent();
    }

}
