<?php

namespace App\Http\Controllers;

use App\Libraries\PaginationLibrary;
use App\Models\Tooltip;
use App\Models\TranslationContent;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
     * @param Request $request
     * @return Collection
     */
    public function index(Request $request): Collection
    {
        // get model
        $tooltip = Tooltip::with(['user']);

        // set pagination information
        $pagination = PaginationLibrary::set($request->input('page'), $tooltip->count(), $request->input('per_page'));

        // get data
        $data = $tooltip
            ->skip($pagination['skip'])
            ->take($pagination['perPage'])
            ->get()
            ->each(function (&$v) {
                $v->lang = $v->contents()->get()->pluck('lang');
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
     *          description="Client authentication failed"
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
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
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
        $translation = $tooltip->translation()->create([
            'explanation' => $request->input('title')
        ]);

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
     *          description="Client authentication failed"
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
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        // update the tooltip
        $tooltip = Tooltip::with('translation')->findOrFail($id);
        $tooltip->update($request->all());

        // update the translation
        $translation = $tooltip->translation()->first();
        $translation->update([
            'explanation' => $request->input('title', $tooltip->title)
        ]);

        if (is_array($content = $request->input('content'))) {
            $translation->translationContents()->each(function ($o) use (&$content) {
                if ($content[$o->lang]) {
                    $o->update(['value' => $content[$o->lang]]);
                    unset($content[$o->lang]);
                }
            });

            foreach($content as $lang => $value) {
                $translation->translationContents()->create([
                    'lang' => $lang,
                    'value' => $value
                ]);
            };
        }
        /*
        $tooltip->translation()->each(function ($o) use ($request, $tooltip) {
            $o->update([
                'explanation' => $request->input('title', $tooltip->title)
            ]);

            if (is_array($content = $request->input('content'))) {
                $o->translationContents()->each(function ($o) use ($content) {
                    if ($content[$o->lang]) {
                        $o->update(['value' => $content[$o->lang]]);
                    }
                });
            }
        });
        */

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
     *          response=403,
     *          description="forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="not found"
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
        $data->contents->each(function ($o) use (&$contents) {
            $contents[$o->lang] = $o->value;
        });
        unset($data->contents);
        $data->setAttribute('contents', $contents);

        // return
        return collect($data);
    }
}
