<?php

namespace App\Http\Controllers;

use App\Exceptions\QpickHttpException;
use App\Http\Requests\Words\IndexRequest;
use App\Http\Requests\Words\RelationStoreRequest;
use App\Http\Requests\Words\ResponseInJsonRequest;
use App\Http\Requests\Words\StoreRequest;
use App\Http\Requests\Words\UpdateRequest;
use App\Libraries\CollectionLibrary;
use App\Libraries\PaginationLibrary;
use App\Models\Word;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;

class WordController extends Controller
{
    public string $exceptionEntity = "word";


    /**
     * @OA\Get(
     *      path="/v1/word",
     *      summary="용어 목록",
     *      description="용어 목록",
     *      operationId="wordIndex",
     *      tags={"용어"},
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
     *                  @OA\Items(type="object", ref="#/components/schemas/RelationWord")
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
        $word = Word::query();

        // Sort By
        if ($s = $request->input('sort_by')) {
            $sortCollect = CollectionLibrary::getBySort($s, ['id']);
            $sortCollect->each(function ($item) use ($word) {
                $word->orderBy($item['key'], $item['value']);
            });
        }

        // Set Pagination Information
        $pagination = PaginationLibrary::set($request->input('page'), $word->count(), $request->input('per_page'));

        // Get Data from DB
        $data = $word->skip($pagination['skip'])->take($pagination['perPage'])->get();

        // Result
        $result = [
            'header' => $pagination ?? [],
            'list' => $data ?? []
        ];

        return collect($result);
    }


    /**
     * @OA\Get(
     *      path="/v1/word/{word_id}",
     *      summary="용어 상세",
     *      description="용어 상세",
     *      operationId="wordShow",
     *      tags={"용어"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/RelationWord")
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
     * @param int $wordId
     * @return Collection
     */
    public function show(int $wordId): Collection
    {
        return collect(Word::findOrFail($wordId));
    }


    /**
     * @param StoreRequest $request
     * @return Collection
     */
    public function store(StoreRequest $request): Collection
    {
        $word = Word::create($request->all());
        return collect($word->refresh());
    }


    /**
     * @OA\Patch(
     *      path="/v1/word/{word_id}",
     *      summary="용어 수정",
     *      description="용어 수정",
     *      operationId="wordUpdate",
     *      tags={"용어"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="code", ref="#/components/schemas/Word/properties/code" ),
     *              @OA\Property(property="title", ref="#/components/schemas/Word/properties/title" ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="created",
     *          @OA\JsonContent(
     *              allOf={
     *                  @OA\Schema(ref="#/components/schemas/RelationWord")
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
     * @param int $wordId
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, int $wordId): JsonResponse
    {
        $wordBuild = Word::query();
        $word = $wordBuild->findOrFail($wordId);

        $word->update($request->all());

        return response()->json(collect($word), 201);
    }


    /**
     * @OA\delete(
     *      path="/v1/word/{word_id}",
     *      summary="용어 삭제",
     *      description="용어 삭제",
     *      operationId="wordDelete",
     *      tags={"용어"},
     *      @OA\Response(
     *          response=204,
     *          description="deleted"
     *      ),
     *      security={{
     *          "admin_auth":{}
     *      }}
     *  )
     * @param int $wordId
     * @return Response
     */
    public function destroy(int $wordId): Response
    {
        $wordBuild = Word::query();
        $word = $wordBuild->findOrFail($wordId);
        $word->delete();

        return response()->noContent();
    }

    /**
     * @OA\Post(
     *      path="/v1/relation-word",
     *      summary="용어 관계형 생성",
     *      description="용어 관계형 작성",
     *      operationId="relationWordCreate",
     *      tags={"용어"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="code", ref="#/components/schemas/Word/properties/code" ),
     *              @OA\Property(property="title", ref="#/components/schemas/Word/properties/title" ),
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
     *                  @OA\Schema(ref="#/components/schemas/RelationWord")
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
        $word = Word::create($request->all());

        $translation = $word->translation()->create($request->all());

        // create a translation content
        if (is_array($content = $request->input('value'))) {
            foreach ($content as $lang => $val) {
                $translation->translationContents()->create([
                    'lang' => $lang,
                    'value' => $val
                ]);
            }
        }

        return collect(word::findOrFail($word->getAttribute('id')));
    }


    /**
     * @OA\Get(
     *      path="/v1/word-to-json",
     *      summary="용어 상세 결과 json 형태로",
     *      description="용어 상세 결과 json 형태로 출력",
     *      operationId="wordToJsonShow",
     *      tags={"용어"},
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

        return collect(__('word'));
    }


}
