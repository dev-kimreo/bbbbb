<?php

namespace App\Http\Controllers;

use App\Http\Requests\Tooltips\CreateRequest;
use App\Http\Requests\Tooltips\IndexRequest;
use App\Http\Requests\Tooltips\UpdateRequest;
use App\Libraries\PaginationLibrary;
use App\Libraries\StringLibrary;
use App\Models\Tooltip;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class TooltipController extends Controller
{
    /**
     * @OA\Get(
     *      path="/v1/tooltip",
     *      summary="툴팁 목록",
     *      description="툴팁 목록",
     *      operationId="TooltipList",
     *      tags={"툴팁"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="page", type="integer", example=1, default=1, description="페이지"),
     *              @OA\Property(property="perPage", type="integer", example=15, default=15, description="한 페이지당 보여질 갯 수"),
     *              @OA\Property(property="hasLang[]", type="string", example="en", description="언어구분 (다중입력 가능)"),
     *              @OA\Property(property="visible", type="boolean", example="true", description="전시여부"),
     *              @OA\Property(property="type", type="string", example="헬프센터", description="전시구분 (한글이름으로 입력)"),
     *              @OA\Property(property="title", type="string", example="도움말 작성버튼", description="제목"),
     *              @OA\Property(property="code", type="string", example="HP_0012", description="전시코드"),
     *              @OA\Property(property="userName", type="string", example="홍길동", description="등록자 이름"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/Pagination"),
     *              @OA\Property(property="list", type="array",
     *                  @OA\Items(type="object", ref="#/components/schemas/TooltipForList")
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
     * Display a listing of the resource.
     *
     * @param IndexRequest $request
     * @return Collection
     */
    public function index(IndexRequest $request): Collection
    {
        // set relations
        $with = ['translation', 'translation.translationContents'];

        if (Auth::hasAccessRightsToBackoffice()) {
            $with[] = 'user';
        }

        // get model
        $tooltip = Tooltip::with($with);

        // set search conditions
        if (is_array($s = $request->input('has_lang'))) {
            // search by contents' language
            foreach ($s as $v) {
                $tooltip->whereHasLanguage($v);
            }
        }

        if (strlen($s = $request->input('visible')) > 0) {
            // search by visibility
            $tooltip->where('visible', ($s && $s != 'false'));
        }

        if ($s = $request->input('type')) {
            // search by display type
            $tooltip->where('type', $s);
        }

        if ($s = $request->input('title')) {
            // search by title
            $tooltip->where('title', 'like', '%' . StringLibrary::escapeSql($s) . '%');
        }

        if ($s = $request->input('code')) {
            // search by title
            $tooltip->whereCodeIs($s);
        }

        if ($s = $request->input('user_name')) {
            // search by writer name
            $tooltip->whereHas('user', function (Builder $q) use ($s) {
                $q->where('name', $s);
            });
        }

        // set pagination information
        $pagination = PaginationLibrary::set($request->input('page'), $tooltip->count(), $request->input('per_page'));

        // get data
        $data = $tooltip
            ->skip($pagination['skip'])
            ->take($pagination['perPage'])
            ->orderByDesc('id')
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
     *      path="/v1/tooltip",
     *      summary="툴팁 작성",
     *      description="툴팁 작성",
     *      operationId="tooltipCreate",
     *      tags={"툴팁"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="type", type="string", example="헬프센터", description="전시구분"),
     *              @OA\Property(property="title", type="string", example="1부터 100 사이의 숫자로 입력", description="툴팁 제목"),
     *              @OA\Property(property="visible", type="integer", example=1, default=1, description="1 또는 0"),
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
     *                  @OA\Schema(ref="#/components/schemas/Tooltip")
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
     *          "davinci_auth":{}
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
        // create a tooltip
        $tooltip = Tooltip::create(
            array_merge(
                $request->all(),
                [
                    'user_id' => Auth::id()
                ]
            )
        );

        // create a translation
        $translation = $tooltip->translation()->create([]);

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
        $data = $this->getOne($tooltip->id);
        return response()->json(collect($data), 201);
    }

    /**
     * @OA\Get(
     *      path="/v1/tooltip/{tooltip_id}",
     *      summary="툴팁 상세",
     *      description="툴팁 상세",
     *      operationId="tooltipShow",
     *      tags={"툴팁"},
     *      @OA\RequestBody(
     *          required=true,
     *          description=""
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              allOf={
     *                  @OA\Schema(ref="#/components/schemas/Tooltip")
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Client authentication failed"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found (열람할 툴팁이 존재하지 않음)"
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
     *      path="/v1/tooltip/{tooltip_id}",
     *      summary="툴팁 수정",
     *      description="툴팁 수정",
     *      operationId="tooltipUpdate",
     *      tags={"툴팁"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="type", type="string", example="헬프센터", description="전시구분"),
     *              @OA\Property(property="title", type="string", example="1부터 100 사이의 숫자로 입력", description="툴팁 제목"),
     *              @OA\Property(property="visible", type="integer", example=1, default=1, description="1 또는 0"),
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
     *                  @OA\Schema(ref="#/components/schemas/Tooltip")
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
     *          "davinci_auth":{}
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
        $tooltip = Tooltip::with('translation')->findOrFail($id);
        $tooltip->update($request->all());

        // update the translation
        if ($translation = $tooltip->translation()->first()) {
            $translation->update([]);
        } else {
            $translation = $tooltip->translation()->create([]);
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
     *      path="/v1/tooltip/{tooltip_id}",
     *      summary="툴팁 삭제",
     *      description="툴팁 삭제",
     *      operationId="tooltipDelete",
     *      tags={"툴팁"},
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
     *          description="Not found (삭제할 툴팁이 존재하지 않음)"
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     *
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy(int $id): Response
    {
        Tooltip::findOrFail($id)->delete();
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
        $data = Tooltip::with($with)->findOrFail($id);

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
