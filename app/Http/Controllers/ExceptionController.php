<?php

namespace App\Http\Controllers;

use App\Exceptions\QpickHttpException;
use App\Http\Requests\Exceptions\IndexRequest;
use App\Http\Requests\Exceptions\RelationStoreRequest;
use App\Http\Requests\Exceptions\StoreRequest;
use App\Http\Requests\Exceptions\UpdateRequest;
use App\Libraries\CollectionLibrary;
use App\Libraries\PaginationLibrary;
use App\Models\Exception;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;

class ExceptionController extends Controller
{
    public string $exceptionEntity = "exception";


    /**
     * @OA\Get(
     *      path="/v1/exception",
     *      summary="예외 목록",
     *      description="예외 목록",
     *      operationId="exceptionIndex",
     *      tags={"예외"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="page", type="integer", example=1, default=1, description="페이지"),
     *              @OA\Property(property="perPage", type="integer", example=15, default=15, description="한 페이지당 보여질 갯 수"),
     *              @OA\Property(property="sortBy", type="string", example="-id", description="정렬기준<br/>+:오름차순, -:내림차순" )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/Pagination"),
     *              @OA\Property(property="list", type="array",
     *                  @OA\Items(type="object", ref="#/components/schemas/RelationException")
     *              )
     *          )
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
     * @param IndexRequest $request
     * @return Collection
     * @throws QpickHttpException
     */
    public function index(IndexRequest $request): Collection
    {
        $exception = Exception::query();

        // Sort By
        if ($s = $request->input('sort_by')) {
            $sortCollect = CollectionLibrary::getBySort($s, ['id']);
            $sortCollect->each(function ($item) use ($exception) {
                $exception->orderBy($item['key'], $item['value']);
            });
        }

        // Set Pagination Information
        $pagination = PaginationLibrary::set($request->input('page'), $exception->count(), $request->input('per_page'));

        // Get Data from DB
        $data = $exception->skip($pagination['skip'])->take($pagination['perPage'])->get();

        // Result
        $result = [
            'header' => $pagination ?? [],
            'list' => $data ?? []
        ];

        return collect($result);
    }

    /**
     * @OA\Get(
     *      path="/v1/exception/{exception_id}",
     *      summary="예외 상세",
     *      description="예외 상세",
     *      operationId="exceptionShow",
     *      tags={"예외"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/RelationException")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "admin_auth":{}
     *      }}
     *  )
     * @param int $exceptionId
     * @return Collection
     */
    public function show(int $exceptionId): Collection
    {
        return collect(Exception::findOrFail($exceptionId));
    }

    /**
     * OA\Post(
     *      path="/v1/exception",
     *      summary="예외 작성",
     *      description="예외 작성",
     *      operationId="exceptionCreate",
     *      tags={"예외"},
     *      OA\RequestBody(
     *          required=true,
     *          description="",
     *          OA\JsonContent(
     *              OA\Property(property="code", type="string", example="common.not_found", description="예외 code"),
     *              OA\Property(property="title", type="string", example="요청한 데이터를 찾을 수 없습니다.", description="예외 제목"),
     *          ),
     *      ),
     *      OA\Response(
     *          response=201,
     *          description="created",
     *          OA\JsonContent(
     *              allOf={
     *                  OA\Schema(ref="#/components/schemas/RelationException")
     *              }
     *          )
     *      ),
     *      OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "admin_auth":{}
     *      }}
     *  )
     *
     * @param StoreRequest $request
     * @return Collection
     */
    public function store(StoreRequest $request): Collection
    {
        $exception = Exception::create($request->all());
        return collect($exception->refresh());
    }

    /**
     * @OA\Patch(
     *      path="/v1/exception/{exception_id}",
     *      summary="예외 수정",
     *      description="예외 수정",
     *      operationId="exceptionUpdate",
     *      tags={"예외"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="code", ref="#/components/schemas/Exception/properties/code" ),
     *              @OA\Property(property="title", ref="#/components/schemas/Exception/properties/title" ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="created",
     *          @OA\JsonContent(
     *              allOf={
     *                  @OA\Schema(ref="#/components/schemas/RelationException")
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "admin_auth":{}
     *      }}
     *  )
     * @param UpdateRequest $request
     * @param int $exceptionId
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, int $exceptionId): JsonResponse
    {
        $exceptionBuild = Exception::query();
        $exception = $exceptionBuild->findOrFail($exceptionId);

        $exception->update($request->all());

        return response()->json(collect($exception), 201);
    }

    /**
     * @OA\delete(
     *      path="/v1/exception/{exception_id}",
     *      summary="예외 삭제",
     *      description="예외 삭제",
     *      operationId="exceptionDelete",
     *      tags={"예외"},
     *      @OA\Response(
     *          response=204,
     *          description="deleted"
     *      ),
     *      security={{
     *          "admin_auth":{}
     *      }}
     *  )
     * @param int $exceptionId
     * @return Response
     */
    public function destroy(int $exceptionId): Response
    {
        $exceptionBuild = Exception::query();
        $exception = $exceptionBuild->findOrFail($exceptionId);
        $exception->delete();

        return response()->noContent();
    }

    /**
     * @OA\Post(
     *      path="/v1/relation-exception",
     *      summary="예외 관계형 생성",
     *      description="예외 관계형 작성",
     *      operationId="relationExceptionCreate",
     *      tags={"예외"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="code", type="string", example="common.not_found", description="예외 code"),
     *              @OA\Property(property="title", type="string", example="요청한 데이터를 찾을 수 없습니다.", description="예외 제목"),
     *              @OA\Property(property="value[ko]", type="string", example="한국어로 입력된 예외 내용", description="한국어로 입력된 내용"),
     *              @OA\Property(property="value[en]", type="string", example="Values written in English", description="영어로 입력된 내용"),
     *              @OA\Property(property="value[..]", type="string", example="다른 어떤 언어로 쓰인 예외 내용", description="[..]안에 쓰인 ISO 639-1코드의 언어로 입력된 내용"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="created",
     *          @OA\JsonContent(
     *              allOf={
     *                  @OA\Schema(ref="#/components/schemas/RelationException")
     *              }
     *          )
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
     * @param RelationStoreRequest $request
     * @return Collection
     */
    public function relationStore(RelationStoreRequest $request): Collection
    {
        $exception = Exception::create($request->all());

        $translation = $exception->translation()->create($request->all());

        // create a translation content
        if (is_array($content = $request->input('value'))) {
            foreach ($content as $lang => $val) {
                $translation->translationContents()->create([
                    'lang' => $lang,
                    'value' => $val
                ]);
            }
        }

        return collect(Exception::findOrFail($exception->getAttribute('id')));
    }


    /**
     * @OA\Get(
     *      path="/v1/exception-to-json",
     *      summary="예외 상세 결과 json 형태로",
     *      description="예외 상세 결과 json 형태로 출력",
     *      operationId="exceptionToJsonShow",
     *      tags={"예외"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "admin_auth":{}
     *      }}
     *  )
     * @return Collection
     */
    public function responseInJsonFormat(): Collection
    {
        Artisan::call('build:translations');

        return collect(__('exception'));
    }
}
