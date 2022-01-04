<?php

namespace App\Http\Controllers\Exhibitions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Exhibitions\Categories\CreateRequest;
use App\Http\Requests\Exhibitions\Categories\UpdateRequest;
use App\Libraries\PaginationLibrary;
use App\Models\Exhibitions\ExhibitionCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class CategoryController extends Controller
{
    public string $exceptionEntity = "exhibitionCategory";

    /**
     * @OA\Get(
     *      path="/v1/exhibition/category",
     *      summary="카테고리 목록",
     *      description="전시관리 카테고리 목록",
     *      operationId="exhibitionCategoryList",
     *      tags={"전시관리"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="page", type="integer", example=1, default=1, description="페이지"),
     *              @OA\Property(property="perPage", type="integer", example=15, default=15, description="한 페이지당 보여질 갯 수"),
     *              @OA\Property(property="enable", type="boolean", example=1, description="사용 가능여부<br />1:사용가능한 항목만 검색<br />0:사용불가한 항목만 검색"),
     *              @OA\Property(property="division", type="string", example="popup", description="popup:팝업용 카테고리<br />banner:배너용 카테고리"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/Pagination"),
     *              @OA\Property(property="list", type="array",
     *                  @OA\Items(type="object", ref="#/components/schemas/ExhibitionCategory")
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
     * @param Request $request
     * @return Collection
     */
    public function index(Request $request): Collection
    {
        // init model
        $category = ExhibitionCategory::orderByDesc('id');

        // search condition
        if ($s = $request->input('enable')) {
            $category->where('enable', $s);
        }

        if ($s = $request->input('division')) {
            $category->where('division', $s);
        }

        // set pagination information
        $pagination = PaginationLibrary::set($request->input('page'), $category->count(), $request->input('per_page'));

        // get data from DB
        $data = $category->skip($pagination['skip'])->take($pagination['perPage'])->get();

        // result
        $result = [
            'header' => $pagination ?? [],
            'list' => $data ?? []
        ];

        return collect($result);
    }

    /**
     * @OA\Post(
     *      path="/v1/exhibition/category",
     *      summary="카테고리 생성",
     *      description="전시관리 카테고리 생성",
     *      operationId="exhibitionCategoryCreate",
     *      tags={"전시관리"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="name", type="string", example="헬프센터 메인 중앙배너", description="카테고리명"),
     *              @OA\Property(property="url", type="url", example="https://help.qpick.com/board/1", description="클릭시 링크될 URL"),
     *              @OA\Property(property="division", type="banner", example="popup", description="popup:팝업용 카테고리<br />banner:배너용 카테고리"),
     *              @OA\Property(property="site", type="string", example="헬프센터", description="사이트명"),
     *              @OA\Property(property="max", type="integer", example=5, description="해당 카테고리에 대해 최대로 표시할 배너/팝업의 개수"),
     *              @OA\Property(property="enable", type="boolean", example=1, description="사용 가능여부<br />1:사용가능<br />0:사용불가"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/ExhibitionCategory")
     *          )
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
     *          "davinci_auth":{},
     *          "admin_auth":{}
     *      }}
     *  )
     *
     * @param CreateRequest $request
     * @return JsonResponse
     */
    public function store(CreateRequest $request): JsonResponse
    {
        $res = ExhibitionCategory::create($request->all());
        return response()->json($this->getOne($res->id), 201);
    }

    /**
     * @OA\Get(
     *      path="/v1/exhibition/category/{category_id}",
     *      summary="카테고리 상세",
     *      description="전시관리 카테고리 상세",
     *      operationId="exhibitionCategoryShow",
     *      tags={"전시관리"},
     *      @OA\RequestBody(
     *          required=true,
     *          description=""
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/ExhibitionCategory")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="not found"
     *      ),
     *      security={{
     *          "davinci_auth":{},
     *          "admin_auth":{}
     *      }}
     *  )
     *
     * @param int $category_id
     * @return Collection
     */
    public function show(int $category_id): Collection
    {
        return $this->getOne($category_id);
    }

    /**
     * @OA\Patch(
     *      path="/v1/exhibition/category/{category_id}",
     *      summary="카테고리 수정",
     *      description="전시관리 카테고리 수정",
     *      operationId="exhibitionCategoryModify",
     *      tags={"전시관리"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="name", type="string", example="헬프센터 메인 중앙배너", description="카테고리명"),
     *              @OA\Property(property="url", type="url", example="https://help.qpick.com/board/1", description="클릭시 링크될 URL"),
     *              @OA\Property(property="division", type="banner", example="popup", description="popup:팝업용 카테고리<br />banner:배너용 카테고리"),
     *              @OA\Property(property="site", type="string", example="헬프센터", description="사이트명"),
     *              @OA\Property(property="max", type="integer", example=5, description="해당 카테고리에 대해 최대로 표시할 배너/팝업의 개수"),
     *              @OA\Property(property="enable", type="boolean", example=1, description="사용 가능여부<br />1:사용가능<br />0:사용불가"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/ExhibitionCategory")
     *          )
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
     *          response=404,
     *          description="not found"
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
     * @param UpdateRequest $request
     * @param int $category_id
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, int $category_id): JsonResponse
    {
        ExhibitionCategory::findOrFail($category_id)->update($request->all());
        return response()->json($this->getOne($category_id), 201);
    }

    /**
     * @OA\delete(
     *      path="/v1/exhibition/category/{category_id}",
     *      summary="카테고리 삭제",
     *      description="전시관리 카테고리 삭제",
     *      operationId="exhibitionCategoryDelete",
     *      tags={"전시관리"},
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
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "admin_auth":{}
     *      }}
     *  )
     *
     * @param int $category_id
     * @return Response
     */
    public function destroy(int $category_id): Response
    {
        ExhibitionCategory::findOrFail($category_id)->delete();
        return response()->noContent();
    }

    protected function getOne(int $category_id): Collection
    {
        return collect(ExhibitionCategory::findOrFail($category_id));
    }
}
