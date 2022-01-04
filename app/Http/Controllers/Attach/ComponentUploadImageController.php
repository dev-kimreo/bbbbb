<?php

namespace App\Http\Controllers\Attach;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attaches\StoreRequest;
use App\Http\Resources\Attach\ComponentUploadImageResource;
use App\Libraries\PaginationLibrary;
use App\Models\Attach\ComponentUploadImage;
use App\Services\AttachService;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Image;

class ComponentUploadImageController extends Controller
{
    public string $exceptionEntity = "componentUploadImage";

    /**
     * @OA\Get(
     *      path="/v1/component-upload-image",
     *      summary="컴포넌트 이미지 목록",
     *      description="로그인된 회원이 테마의 컴포넌트에 사용하기 위해 업로드한 이미지 목록",
     *      operationId="componentUploadImageList",
     *
     *      tags={"첨부파일"},
     *      @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="page", type="integer", example=1, description="페이지" ),
     *              @OA\Property(property="perPage", type="integer", example=15, description="한 페이지에 보여질 수" ),
     *              @OA\Property(
     *                  property="sortBy",
     *                  type="string",
     *                  example="+sort,-id",
     *                  description="정렬기준<br/>+:오름차순, -:내림차순"
     *              )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/Pagination" ),
     *              @OA\Property(property="list", type="array",
     *                  @OA\Items(
     *                      allOf={@OA\Schema(ref="#/components/schemas/AttachFile")},
     *                      @OA\Property(property="componentUploadImage", ref="#/components/schemas/ComponentUploadImage")
     *                  )
     *              )
     *          )
     *      )
     *  )
     *
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Collection
     */
    public function index(Request $request): Collection
    {
        // init model
        $model = ComponentUploadImage::query()->orderByDesc('id');

        if (!Auth::isLoggedForBackoffice()) {
            $model->where('user_id', Auth::id());
        }

        // set pagination information
        $pagination = PaginationLibrary::set($request->input('page'), $model->count(), $request->input('per_page'));

        // get ids from DB
        $data = $model->skip($pagination['skip'])->take($pagination['perPage'])->get();

        // result
        $result = [
            'header' => $pagination ?? [],
            'list' => ComponentUploadImageResource::collection($data) ?? []
        ];

        return collect($result);
    }

    /**
     * @OA\Post(
     *      path="/v1/component-upload-image",
     *      summary="컴포넌트 이미지 업로드",
     *      description="회원 테마의 컴포넌트에 사용하기 위한 이미지 업로드",
     *      operationId="componentUploadImageCreate",
     *      tags={"첨부파일"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="files", type="string", description="", format="binary"
     *                  )
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="created",
     *          @OA\JsonContent(
     *              allOf={@OA\Schema(ref="#/components/schemas/AttachFile")},
     *              @OA\Property(property="componentUploadImage", ref="#/components/schemas/ComponentUploadImage")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="bad request"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     *
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @param AttachService $attachService
     * @return JsonResponse
     * @throws QpickHttpException
     */
    public function store(StoreRequest $request, AttachService $attachService): JsonResponse
    {
        // Getting width and height
        $image = Image::make($request->file('files'));

        // Create
        $attach = $attachService->create($request->file('files'))->refresh();
        $res = ComponentUploadImage::query()->create(
            [
                'user_id' => Auth::id(),
                'width' => $image->getWidth(),
                'height' => $image->getHeight()
            ]
        );

        // Move
        $attachService->move($res, [$attach->getAttribute('id')]);

        // Response
        return response()->json($this->getOneResponse($res->getAttribute('id')), 201);
    }

    /**
     * @OA\Get(
     *      path="/v1/component-upload-image/{id}",
     *      summary="컴포넌트 이미지 상세 정보",
     *      description="회원 테마의 컴포넌트에 사용하기 위한 이미지 상세 정보",
     *      operationId="componentUploadImageInfo",
     *      tags={"첨부파일"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              allOf={@OA\Schema(ref="#/components/schemas/AttachFile")},
     *              @OA\Property(property="componentUploadImage", ref="#/components/schemas/ComponentUploadImage")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *  )
     *
     * Display the specified resource.
     *
     * @param $id
     * @return Collection
     * @throws QpickHttpException
     */
    public function show($id): Collection
    {
        return $this->getOneResponse($id);
    }

    /**
     * @OA\Delete(
     *      path="/v1/component-upload-image/{id}",
     *      summary="컴포넌트 이미지 삭제",
     *      description="회원 테마의 컴포넌트에 사용하기 위해 업로드한 이미지 삭제",
     *      operationId="componentUploadImageDelete",
     *      tags={"첨부파일"},
     *      @OA\Response(
     *          response=204,
     *          description="deleted."
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
     *
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     * @throws QpickHttpException
     */
    public function destroy(int $id): Response
    {
        $this->getOneModel($id)->delete();
        return response()->noContent();
    }

    /**
     * @param int $id
     * @return Model
     * @throws QpickHttpException
     */
    protected function getOneModel(int $id): Model
    {
        $res = ComponentUploadImage::findOrFail($id);

        if (!Auth::isLoggedForBackoffice() && $res->getAttribute('user_id') != Auth::id()) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        return $res;
    }

    /**
     * @param int $id
     * @return Collection
     * @throws QpickHttpException
     */
    protected function getOneResponse(int $id): Collection
    {
        return collect(ComponentUploadImageResource::make($this->getOneModel($id)));
    }
}
