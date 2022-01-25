<?php

namespace App\Http\Controllers\Attach;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attaches\StoreRequest;
use App\Http\Resources\Attach\ComponentUploadImageResource;
use App\Libraries\PaginationLibrary;
use App\Models\Attach\ComponentUploadImage;
use App\Models\Users\User;
use App\Services\AttachService;
use App\Services\UserService;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Image;
use JetBrains\PhpStorm\ArrayShape;

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
     * @OA\Get(
     *      path="/v1/component-upload-image/usage",
     *      summary="컴포넌트 이미지 사용량 조회",
     *      description="사용자가 업로드한 컴포넌트 이미지의 개수 및 용량을 조회",
     *      operationId="componentUploadImageUsage",
     *      tags={"첨부파일"},
     *      @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="user_id", type="integer", example=173, default="로그인한 회원의 ID", description="회원 ID (백오피스 로그인시에만 사용가능)<br />")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="count", type="integer", example=3, description="업로드한 컴포넌트 이미지 총 개수" ),
     *              @OA\Property(property="storage", type="object",
     *                  @OA\Property(property="usage", type="integer", example=204152, description="업로드한 컴포넌트 이미지 용량 합계(byte 단위)"),
     *                  @OA\Property(property="limit", type="integer", example=104857600, description="업로드할 수 있는 컴포넌트 이미지 용량제한(byte 단위)")
     *              ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *  )
     *
     * @param Request $req
     * @return JsonResponse
     */
    public function usage(Request $req): JsonResponse
    {
        // Set Target User ID
        if(Auth::isLoggedForBackoffice()) {
            $userId = $req->get('user_id') ?? Auth::id();
        } else {
            $userId = Auth::id();
        }

        // Get values
        list($cnt, $sum) = $this->getStorageUsage($userId);
        $limit = $this->getStorageLimit($userId);

        // Response
        $res = [
            'count' => $cnt,
            'storage' => [
                'usage' => $sum,
                'limit' => $limit
            ]
        ];
        return response()->json($res, 201);
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

    /**
     * @param int $userId
     * @return bool
     */
    protected function chkUnderStorageLimit(int $userId): bool
    {
        $limit = $this->getStorageLimit($userId);
        list($cnt, $sum) = $this->getStorageUsage($userId);

        return $sum < $limit;
    }

    /**
     * @param int $userId
     * @return array
     */
    protected function getStorageUsage(int $userId): array
    {
        // Initialize
        $cnt = 0;
        $sum = 0;

        // Query and aggregation
        ComponentUploadImage::query()
            ->where('user_id', $userId)
            ->get()
            ->each(function ($v) use (&$cnt, &$sum) {
                $cnt++;
                $sum += intval($v->attachFile->size);
            });

        return [$cnt, $sum];
    }

    /**
     * @param int $userId
     * @return int
     */
    protected function getStorageLimit(int $userId): int
    {
        $pricingTypeCode = UserService::getPricingType(User::find($userId));
        $pricingTypeName = array_flip(config('custom.user.pricingType'))[$pricingTypeCode];
        return intval(config('custom.attach.componentUploadImage.totalStorageLimit')[$pricingTypeName]);
    }
}
