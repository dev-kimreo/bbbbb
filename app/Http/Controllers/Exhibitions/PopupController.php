<?php

namespace App\Http\Controllers\Exhibitions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Exhibitions\Popups\CreateRequest;
use App\Http\Requests\Exhibitions\Popups\IndexRequest;
use App\Http\Requests\Exhibitions\Popups\UpdateRequest;
use App\Libraries\PaginationLibrary;
use App\Libraries\StringLibrary;
use App\Models\Exhibitions\ExhibitionTargetUser;
use App\Models\Exhibitions\Popup;
use App\Models\Exhibitions\PopupDeviceContent;
use App\Models\Users\User;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PopupController extends Controller
{
    public string $exceptionEntity = "popup";

    /**
     * @OA\Get(
     *      path="/v1/exhibition/popup",
     *      summary="팝업 목록",
     *      description="팝업 목록",
     *      operationId="exhibitionPopupList",
     *      tags={"전시관리"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="page", type="integer", example=1, default=1, description="페이지"),
     *              @OA\Property(property="perPage", type="integer", example=15, default=15, description="한 페이지당 보여질 갯 수"),
     *              @OA\Property(property="category", type="integer", example="372", description="전시관리카테고리 번호"),
     *              @OA\Property(property="title", type="string", example="7월", description="팝업 제목"),
     *              @OA\Property(property="startDate", type="datetime", example="2021-07-01T00:00:00+00:00", description="전시기간 검색 시작일"),
     *              @OA\Property(property="endDate", type="datetime", example="2021-07-01T23:59:59+00:00", description="전시기간 검색 종료일"),
     *              @OA\Property(property="device", type="string", example="both", description="디바이스 선택<br />both:양쪽 모두 선택된 팝업<br />pc:PC만 선택된 팝업<br />mobile:모바일만 선택된 팝업"),
     *              @OA\Property(property="targetOpt[]", type="array of string", example="all", description="전시 타겟설정<br />all:모든 회원<br />grade:회원구분<br />designate:특정회원"),
     *              @OA\Property(property="visible", type="boolean", example=1, description="전시여부<br />1:보임으로 설정한 팝업만 검색<br />0:숨김으로 설정한 팝업만 검색"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/Pagination"),
     *              @OA\Property(property="list", type="array",
     *                  @OA\Items(type="object", ref="#/components/schemas/ExhibitionPopupForList")
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
     * @param IndexRequest $request
     * @return Collection
     */
    public function index(IndexRequest $request): Collection
    {
        // init model
        $popup = Popup::orderByDesc('id');

        // search condition
        if ($s = $request->input('category')) {
            $popup->whereHasCategory($s);
        }

        if ($s = $request->input('title')) {
            $popup->where('title', 'like', '%' . StringLibrary::escapeSql($s) . '%');
        }

        if ($s = $request->input('start_date')) {
            $s = Carbon::parse($s);
            $popup->whereHas('exhibition', function ($q) use ($s) {
                $q->where('ended_at', '>=', $s);
            });
        }

        if ($s = $request->input('end_date')) {
            $s = Carbon::parse($s)->setTime(23, 59, 59);
            $popup->whereHas('exhibition', function ($q) use ($s) {
                $q->where('started_at', '<=', $s);
            });
        }

        if ($s = $request->input('device')) {
            $func = in_array($s, ['mobile', 'both'])? 'whereHas': 'whereDoesntHave';
            $popup->$func('contents', function ($q) {
                $q->where('device', 'mobile');
            });

            $func = in_array($s, ['pc', 'both'])? 'whereHas': 'whereDoesntHave';
            $popup->$func('contents', function ($q) {
                $q->where('device', 'pc');
            });
        }


        if (Auth::check()) {
            if (Auth::hasAccessRightsToBackoffice()) {
                if (is_array($s = $request->input('target_opt'))) {
                    $popup->whereHas('exhibition', function ($q) use ($s) {
                        $q->whereIn('target_opt', $s);
                    });
                }

                if (strlen($s = $request->input('visible'))) {
                    $popup->whereHas('exhibition', function ($q) use ($s) {
                        $q->where('visible', $s);
                    });
                }
            } else {
                $popup->whereHas('exhibition', function ($q) {
                    $q->where('target_opt', 'all')
                        ->orWhere(function ($oq) {
                            $oq->where('target_opt', 'grade')->whereJsonContains('target_grade', User::$userGrade[Auth::user()->grade]);
                        })
                        ->orWhere(function ($oq) {
                            $oq->where('target_opt', 'designate')->whereHas('targetUsers', function ($hq) {
                                $hq->where('user_id', Auth::id());
                            });
                        })
                        ->where('visible', 1);
                });
            }
        } else {
            $popup->whereHas('exhibition', function ($q) {
                $q->where('target_opt', 'all')
                    ->where('visible', 1);
            });
        }


        // set pagination information
        $pagination = PaginationLibrary::set($request->input('page'), $popup->count(), $request->input('per_page'));

        // get data from DB
        $data = $popup->skip($pagination['skip'])->take($pagination['perPage'])->get();

        $data->each(function(&$v) {
            $v->setHidden(['user_id', 'deleted_at']);
            $v->setAppends(['devices']);
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
     *      path="/v1/exhibition/popup",
     *      summary="팝업 생성",
     *      description="팝업 생성",
     *      operationId="exhibitionPopupCreate",
     *      tags={"전시관리"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="exhibition_category_id", type="integer", example=5, description="전시관리 카테고리 고유번호(PK)"),
     *              @OA\Property(property="title", type="string", example="7월 광고팝업", description="팝업 제목"),
     *              @OA\Property(property="startedAt", type="string", format="date-time", description="전시기간 시작일자"),
     *              @OA\Property(property="endedAt", type="string", format="date-time", description="전시기간 종료일자"),
     *              @OA\Property(property="sort", type="integer", example=999, description="전시순서"),
     *              @OA\Property(property="visible", type="boolean", example=true, description="전시여부<br />(1:노출, 0:숨김)"),
     *              @OA\Property(property="targetOpt", type="string", example="grade", description="전시 타겟설정<br />all:모든 회원<br />grade:회원구분<br />designate:특정회원"),
     *              @OA\Property(property="targetGrade[]", type="array of string", example="associate", description="타겟설정을 위한 회원등급, 타겟설정이 [회원구분]인 경우에 입력<br />associate:준회원<br />regular:정회원"),
     *              @OA\Property(property="targetUsers[]", type="array of integer", example="74", description="타겟설정을 위한 회원 고유번호(PK), 타겟설정이 [회원구분]인 경우에 입력"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ExhibitionPopup")
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
        $popup = Popup::create(array_merge($request->all(), ['user_id' => Auth::id()]));
        $exhibition = $popup->exhibition()->create($request->all());

        if ($request->input('target_opt') == 'designate') {
            foreach ($request->input('target_users') ?? [] as $v) {
                $exhibition->targetUsers()->create(['user_id' => $v]);
            }
        }

        if (is_array($request->input('contents'))) {
            foreach ($request->input('contents') ?? [] as $k => $v) {
                if ($v) {
                    $popup->contents()->create(['device' => $k, 'contents' => $v]);
                }
            }
        }

        return response()->json($this->getOne($popup->id), 201);
    }

    /**
     * @OA\Get(
     *      path="/v1/exhibition/popup/{popup_id}",
     *      summary="팝업 상세",
     *      description="팝업 상세",
     *      operationId="exhibitionPopupShow",
     *      tags={"전시관리"},
     *      @OA\RequestBody(
     *          required=true,
     *          description=""
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ExhibitionPopup")
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
     * @param $popup_id
     * @return Collection
     */
    public function show($popup_id): Collection
    {
        return $this->getOne($popup_id);
    }

    /**
     * @OA\Patch(
     *      path="/v1/exhibition/popup/{popup_id}",
     *      summary="팝업 수정",
     *      description="팝업 수정",
     *      operationId="exhibitionPopupEdit",
     *      tags={"전시관리"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="exhibition_category_id", type="integer", example=5, description="전시관리 카테고리 고유번호(PK)"),
     *              @OA\Property(property="title", type="string", example="7월 광고팝업", description="팝업 제목"),
     *              @OA\Property(property="url", type="url", example="https://help.qpick.com/board/1", description="링크 URL"),
     *              @OA\Property(property="gaCode", type="url", example="27bf63c92ced856d1b931162a15383e3", description="구글 애널리틱스 트래킹 코드"),
     *              @OA\Property(property="memo", type="string", example="7월 회원가입 이벤트 광고팝업", description="팝업 설명"),
     *              @OA\Property(property="startedAt", type="string", format="date-time", description="전시기간 시작일자"),
     *              @OA\Property(property="endedAt", type="string", format="date-time", description="전시기간 종료일자"),
     *              @OA\Property(property="sort", type="integer", example=999, description="전시순서"),
     *              @OA\Property(property="visible", type="boolean", example=true, description="전시여부<br />(1:노출, 0:숨김)"),
     *              @OA\Property(property="targetOpt", type="string", example="grade", description="전시 타겟설정<br />all:모든 회원<br />grade:회원구분<br />designate:특정회원"),
     *              @OA\Property(property="targetGrade[]", type="array of string", example="associate", description="타겟설정을 위한 회원등급, 타겟설정이 [회원구분]인 경우에 입력<br />associate:준회원<br />regular:정회원"),
     *              @OA\Property(property="targetUsers[]", type="array of integer", example="74", description="타겟설정을 위한 회원 고유번호(PK), 타겟설정이 [회원구분]인 경우에 입력"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ExhibitionPopup")
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
     *      security={{
     *          "davinci_auth":{},
     *          "admin_auth":{}
     *      }}
     *  )
     *
     * @param UpdateRequest $request
     * @param int $popup_id
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, int $popup_id): JsonResponse
    {
        $popup = Popup::findOrFail($popup_id);
        $popup->update($request->all());
        $popup->exhibition->update($request->all());

        // Target User Update
        if (($request->input('target_opt') ?? $popup->exhibition->target_opt) == 'designate') {
            if ($request->input('target_users')) {
                ExhibitionTargetUser::where('exhibition_id', $popup->exhibition->id)
                    ->whereNotIn('user_id', $request->input('target_users'))
                    ->delete();

                foreach ($request->input('target_users') ?? [] as $v) {
                    ExhibitionTargetUser::updateOrCreate(
                        ['exhibition_id' => $popup->exhibition->id, 'user_id' => $v],
                        ['user_id' => $v]
                    );
                }
            }
        } else {
            $popup->targetUsers()->delete();
        }

        // Target Grade Update
        if (($request->input('target_opt') ?? $popup->exhibition->target_opt) == 'grade') {
            if ($request->input('target_grade')) {
                $popup->exhibition->update(['target_grade' => $request->input('target_grade')]);
            }
        } else {
            $popup->exhibition->update(['target_grade' => null]);
        }

        if (is_array($request->input('contents'))) {
            foreach ($request->input('contents') ?? [] as $k => $v) {
                if ($v) {
                    PopupDeviceContent::withTrashed()
                        ->updateOrCreate(['popup_id' => $popup_id, 'device' => $k], ['contents' => $v])
                        ->restore();
                } else {
                    PopupDeviceContent::where('popup_id', $popup_id)
                        ->where('device', $k)
                        ->first()
                        ->delete();
                }
            }
        }

        return response()->json($this->getOne($popup_id), 201);
    }

    /**
     * @OA\delete(
     *      path="/v1/exhibition/popup/{popup_id}",
     *      summary="팝업 삭제",
     *      description="팝업 삭제",
     *      operationId="exhibitionPopupDelete",
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
     * @param int $popup_id
     * @return Response
     */
    public function destroy(int $popup_id): Response
    {
        Popup::findOrFail($popup_id)->delete();
        return response()->noContent();
    }

    protected function getOne(int $category_id): Collection
    {
        $with = ['exhibition', 'exhibition.category', 'contents', 'creator'];
        return collect(Popup::with($with)->findOrFail($category_id));
    }
}
