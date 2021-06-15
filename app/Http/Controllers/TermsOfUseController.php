<?php

namespace App\Http\Controllers;

use App\Exceptions\QpickHttpException;
use App\Http\Requests\TermsOfUse\CreateRequest;
use App\Http\Requests\TermsOfUse\IndexRequest;
use App\Http\Requests\TermsOfUse\UpdateRequest;
use App\Libraries\CollectionLibrary;
use App\Libraries\PaginationLibrary;
use App\Models\TermsOfUse;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class TermsOfUseController extends Controller
{
    private TermsOfUse $termsOfUse;

    public function __construct(TermsOfUse $termsOfUse)
    {
        $this->termsOfUse = $termsOfUse;
    }


    /**
     * @OA\Get(
     *      path="/v1/terms-of-use",
     *      summary="이용약관&개인정보처리방침 목록",
     *      description="이용약관&개인정보처리방침 목록",
     *      operationId="termsOfUseIndex",
     *      tags={"이용약관&개인정보처리방침"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="page", type="integer", example=1, default=1, description="페이지"),
     *              @OA\Property(property="perPage", type="integer", example=15, default=15, description="한 페이지당 보여질 갯 수"),
     *              @OA\Property(property="hasLang[]", type="string", example="en", description="언어구분 (다중입력 가능)"),
     *              @OA\Property(property="type", type="string", example="이용약관", description="구분 (이용약관, 개인정보처리방침)"),
     *              @OA\Property(property="sortBy", type="string", example="-id", description="정렬기준<br/>+:오름차순, -:내림차순" )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/Pagination"),
     *              @OA\Property(property="list", type="array",
     *                  @OA\Items(type="object", ref="#/components/schemas/TermsOfUseForList")
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
     * Display a listing of the resource.
     *
     * @param IndexRequest $request
     * @return Collection
     * @throws QpickHttpException
     */
    public function index(IndexRequest $request): Collection
    {
        // set relations
        $with = ['translation', 'translation.translationContents'];

        $terms = $this->termsOfUse->with($with);

        if ($s = $request->input('type')) {
            $terms->where('type', $s);
        }

        // set search conditions
        if (is_array($s = $request->input('has_lang'))) {
            // search by contents' language
            foreach ($s as $v) {
                $terms->whereHasLanguage($v);
            }
        }

        // Sort By
        if ($s = $request->input('sort_by')) {
            $sortCollect = CollectionLibrary::getBySort($s, ['id']);
            $sortCollect->each(function ($item) use ($terms) {
                $terms->orderBy($item['key'], $item['value']);
            });
        }

        // set pagination information
        $pagination = PaginationLibrary::set($request->input('page'), $terms->count(), $request->input('per_page'));

        $data = $terms
            ->skip($pagination['skip'])
            ->take($pagination['perPage'])
            ->get()
            ->each(function (&$v) {
                $v->lang = $v->translation->translationContents->pluck('lang');
                unset($v->translation);
            });

        // result
        $result = [
            'header' => $pagination ?? [],
            'list' => $data ?? []
        ];

        return collect($result);
    }

    /**
     * @OA\Post(
     *      path="/v1/terms-of-use",
     *      summary="이용약관&개인정보처리방침 작성",
     *      description="이용약관&개인정보처리방침 작성",
     *      operationId="termsOfUseCreate",
     *      tags={"이용약관&개인정보처리방침"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="type", type="string", example="헬프센터", description="전시구분"),
     *              @OA\Property(property="title", type="string", example="1부터 100 사이의 숫자로 입력", description="툴팁 제목"),
     *              @OA\Property(property="startDate", type="date(Y-m-d H:i:s)", example="2021-06-01 09:00:00", description="전시 시작일"),
     *              @OA\Property(property="history", type="string", example="변경내역", description="변경내역"),
     *              @OA\Property(property="content[ko]", type="string", example="한국어로 입력된 툴팁 내용", description="한국어로 입력된 툴팁 내용"),
     *              @OA\Property(property="content[en]", type="string", example="Contents written in English", description="영어로 입력된 툴팁 내용"),
     *              @OA\Property(property="content[..]", type="string", example="다른 어떤 언어로 쓰인 툴팁 내용", description="[..]안에 쓰인 ISO 639-1코드의 언어로 입력된 툴팁 내용"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="created",
     *          @OA\JsonContent(
     *              allOf={
     *                  @OA\Schema(ref="#/components/schemas/TermsOfUse")
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated (비로그인)"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden (백오피스 로그인시에만 가능)"
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
     * Store a newly created resource in storage.
     *
     * @param CreateRequest $request
     * @return JsonResponse
     */
    public function store(CreateRequest $request): JsonResponse
    {
        // create a terms
        $terms = $this->termsOfUse::create(
            array_merge(
                $request->all(),
                [
                    'start_date' => Carbon::parse($request->input('start_date')),
                    'user_id' => Auth::id()
                ]
            )
        );

        // create a translation
        $translation = $terms->translation()->create([]);

        // create a translation content
        if (is_array($content = $request->input('content'))) {
            foreach ($content as $lang => $value) {
                $translation->translationContents()->create([
                    'lang' => $lang,
                    'value' => $value
                ]);
            }
        }

        // response
        $data = $this->getOne($terms->id);
        return response()->json(collect($data), 201);
    }


    /**
     * @OA\Get(
     *      path="/v1/terms-of-use/{terms_of_use_id}",
     *      summary="이용약관&개인정보처리방침 상세",
     *      description="이용약관&개인정보처리방침 상세",
     *      operationId="termsOfUseShow",
     *      tags={"이용약관&개인정보처리방침"},
     *      @OA\RequestBody(
     *          required=true,
     *          description=""
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              allOf={
     *                  @OA\Schema(ref="#/components/schemas/TermsOfUse")
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Client authentication failed"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found"
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
     * Display the specified resource.
     *
     * @param int $id
     * @return Collection
     */
    public function show(int $id): Collection
    {
        return $this->getOne($id);
    }

    /**
     * @OA\Patch(
     *      path="/v1/terms-of-use/{terms_of_use_id}",
     *      summary="이용약관&개인정보처리방침 수정",
     *      description="이용약관&개인정보처리방침 수정",
     *      operationId="termsOfUseUpdate",
     *      tags={"이용약관&개인정보처리방침"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="type", type="string", example="헬프센터", description="전시구분"),
     *              @OA\Property(property="title", type="string", example="1부터 100 사이의 숫자로 입력", description="툴팁 제목"),
     *              @OA\Property(property="startDate", type="date(Y-m-d H:i:s)", example="2021-06-01 09:00:00", description="전시 시작일"),
     *              @OA\Property(property="history", type="string", example="변경내역", description="변경내역"),
     *              @OA\Property(property="content[ko]", type="string", example="한국어로 입력된 툴팁 내용", description="한국어로 입력된 툴팁 내용"),
     *              @OA\Property(property="content[en]", type="string", example="Contents written in English", description="영어로 입력된 툴팁 내용"),
     *              @OA\Property(property="content[..]", type="string", example="다른 어떤 언어로 쓰인 툴팁 내용", description="[..]안에 쓰인 ISO 639-1코드의 언어로 입력된 툴팁 내용"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="created",
     *          @OA\JsonContent(
     *              allOf={
     *                  @OA\Schema(ref="#/components/schemas/TermsOfUse")
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated (비로그인)"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden (백오피스 로그인시에만 가능)"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found (수정할 툴팁이 존재하지 않음)"
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
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        // update the tooltip
        $terms = $this->termsOfUse::with('translation')->findOrFail($id);

        // 전시시작일 이후일 경우 수정 불가
        if (Carbon::now()->format('c') >= $terms->start_date->format('c')) {
            throw new QpickHttpException(422, 'terms.disable.modify.over.start_date');
        }

        $terms->update($request->all());

        // update the translation
        if ($translation = $terms->translation()->first()) {
            $translation->update([]);
        } else {
            $translation = $terms->translation()->create([]);
        }

        if (is_array($content = $request->input('content'))) {
            $translation->translationContents()->each(function ($o) use (&$content) {
                if (isset($content[$o->lang])) {
                    $o->update(['value' => $content[$o->lang]]);
                    unset($content[$o->lang]);
                }
            });

            foreach ($content as $lang => $value) {
                $translation->translationContents()->create([
                    'lang' => $lang,
                    'value' => $value
                ]);
            };
        }

        // response
        $data = $this->getOne($id);
        return response()->json(collect($data), 201);
    }

    /**
     * @OA\delete(
     *      path="/v1/terms-of-use/{terms_of_use_id}",
     *      summary="이용약관&개인정보처리방침 삭제",
     *      description="이용약관&개인정보처리방침 삭제",
     *      operationId="termsOfUseDelete",
     *      tags={"이용약관&개인정보처리방침"},
     *      @OA\Response(
     *          response=204,
     *          description="deleted"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated (비로그인)"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden (백오피스 로그인시에만 가능)"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found"
     *      ),
     *      security={{
     *          "admin_auth":{}
     *      }}
     *  )
     *
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy(int $id): Response
    {
        //
        $terms = $this->termsOfUse::findOrFail($id);

        // 전시시작일 이후일 경우 삭제 불가
        if (Carbon::now()->format('c') >= $terms->start_date->format('c')) {
            throw new QpickHttpException(422, 'terms.disable.modify.over.start_date');
        }

        $terms->delete();
        return response()->noContent();
    }


    protected function getOne($id): Collection
    {
        // set relations
        $with = [];

        if (Auth::hasAccessRightsToBackoffice()) {
            $with[] = 'user';
            $with[] = 'backofficeLogs';
        }

        // get data
        $data = $this->termsOfUse::with($with)->findOrFail($id);

        // post processing
        $contents = [];
        $data->translation->translationContents->each(function ($o) use (&$contents) {
            $contents[$o->lang] = $o->value;
        });
        $data->setAttribute('contents', $contents);
        unset($data->translation);

        // return
        return collect($data);
    }
}
