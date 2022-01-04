<?php

namespace App\Http\Controllers\Exhibitions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Exhibitions\Banners\CreateRequest;
use App\Http\Requests\Exhibitions\Banners\IndexRequest;
use App\Http\Requests\Exhibitions\Banners\UpdateRequest;
use App\Libraries\PaginationLibrary;
use App\Libraries\StringLibrary;
use App\Models\Exhibitions\Banner;
use App\Models\Exhibitions\BannerDeviceContent;
use App\Models\Exhibitions\ExhibitionTargetUser;
use App\Models\Users\User;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class BannerController extends Controller
{
    public string $exceptionEntity = "banner";

    /**
     * @OA\Get(
     *      path="/v1/exhibition/banner",
     *      summary="배너 목록",
     *      description="배너 목록",
     *      operationId="exhibitionBannerList",
     *      tags={"전시관리"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="page", type="integer", example=1, default=1, description="페이지"),
     *              @OA\Property(property="perPage", type="integer", example=15, default=15, description="한 페이지당 보여질 갯 수"),
     *              @OA\Property(property="category", type="integer", example="372", description="전시관리카테고리 번호"),
     *              @OA\Property(property="title", type="string", example="7월", description="배너 제목"),
     *              @OA\Property(property="startDate", type="date(Y-m-d)", example="2021-07-01", description="전시기간 검색 시작일"),
     *              @OA\Property(property="endDate", type="date(Y-m-d)", example="2021-07-01", description="전시기간 검색 종료일"),
     *              @OA\Property(property="device", type="string", example="both", description="디바이스 선택<br />both:양쪽 모두 선택된 배너<br />pc:PC만 선택된 배너<br />mobile:모바일만 선택된 배너"),
     *              @OA\Property(property="targetOpt[]", type="array of string", example="all", description="전시 타겟설정<br />all:모든 회원<br />grade:회원구분<br />designate:특정회원"),
     *              @OA\Property(property="visible", type="boolean", example=1, description="전시여부<br />1:보임으로 설정한 배너만 검색<br />0:숨김으로 설정한 배너만 검색"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/Pagination"),
     *              @OA\Property(property="list", type="array",
     *                  @OA\Items(type="object", ref="#/components/schemas/ExhibitionBannerForList")
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
        $banner = Banner::orderByDesc('id');

        // search condition
        if ($s = $request->input('category')) {
            $banner->whereHasCategory($s);
        }

        if ($s = $request->input('title')) {
            $banner->where('title', 'like', '%' . StringLibrary::escapeSql($s) . '%');
        }

        if ($s = $request->input('device')) {
            $func = in_array($s, ['mobile', 'both']) ? 'whereHas' : 'whereDoesntHave';
            $banner->$func('contents', function ($q) {
                $q->where('device', 'mobile');
            });

            $func = in_array($s, ['pc', 'both']) ? 'whereHas' : 'whereDoesntHave';
            $banner->$func('contents', function ($q) {
                $q->where('device', 'pc');
            });
        }

        if ($s = $request->input('start_date')) {
            $s = Carbon::parse($s);
            $banner->whereHas('exhibition', function ($q) use ($s) {
                $q->where('ended_at', '>=', $s);
            });
        }

        if ($s = $request->input('end_date')) {
            $s = Carbon::parse($s)->setTime(23, 59, 59);
            $banner->whereHas('exhibition', function ($q) use ($s) {
                $q->where('started_at', '<=', $s);
            });
        }

        if (Auth::check()) {
            if (Auth::hasAccessRightsToBackoffice()) {
                if (is_array($s = $request->input('target_opt'))) {
                    $banner->whereHas('exhibition', function ($q) use ($s) {
                        $q->whereIn('target_opt', $s);
                    });
                }

                if (strlen($s = $request->input('visible'))) {
                    $banner->whereHas('exhibition', function ($q) use ($s) {
                        $q->where('visible', $s);
                    });
                }
            } else {
                $banner->whereHas('exhibition', function ($q) {
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
            $banner->whereHas('exhibition', function ($q) {
                $q->where('target_opt', 'all')
                    ->where('visible', 1);
            });
        }


        // set pagination information
        $pagination = PaginationLibrary::set($request->input('page'), $banner->count(), $request->input('per_page'));

        // get data from DB
        $data = $banner->skip($pagination['skip'])->take($pagination['perPage'])->get();

        $data->each(function (&$v) {
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
     *      path="/v1/exhibition/banner",
     *      summary="배너 생성",
     *      description="배너 생성",
     *      operationId="exhibitionBannerCreate",
     *      tags={"전시관리"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="exhibition_category_id", type="integer", example=5, description="전시관리 카테고리 고유번호(PK)"),
     *              @OA\Property(property="title", type="string", example="7월 광고배너", description="배너 제목"),
     *              @OA\Property(property="url", type="url", example="https://help.qpick.com/board/1", description="링크 URL"),
     *              @OA\Property(property="gaCode", type="url", example="27bf63c92ced856d1b931162a15383e3", description="구글 애널리틱스 트래킹 코드"),
     *              @OA\Property(property="memo", type="string", example="7월 회원가입 이벤트 광고배너", description="배너 설명"),
     *              @OA\Property(property="startedAt", type="string", format="date-time", description="전시기간 시작일자"),
     *              @OA\Property(property="endedAt", type="string", format="date-time", description="전시기간 종료일자"),
     *              @OA\Property(property="sort", type="integer", example=999, description="전시순서"),
     *              @OA\Property(property="visible", type="boolean", example=true, description="전시여부<br />true:노출<br />false:숨김"),
     *              @OA\Property(property="targetOpt", type="string", example="grade", description="전시 타겟설정<br />all:모든 회원<br />grade:회원구분<br />designate:특정회원"),
     *              @OA\Property(property="targetGrade[]", type="array of string", example="associate", description="타겟설정을 위한 회원등급, 타겟설정이 [회원구분]인 경우에 입력<br />associate:준회원<br />regular:정회원"),
     *              @OA\Property(property="targetUsers[]", type="array of integer", example="74", description="타겟설정을 위한 회원 고유번호(PK), 타겟설정이 [회원구분]인 경우에 입력"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ExhibitionBanner")
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
        $banner = Banner::create(array_merge($request->all(), ['user_id' => Auth::id()]));
        $exhibition = $banner->exhibition()->create($request->all());

        if ($request->input('target_opt') == 'designate') {
            foreach ($request->input('target_users') ?? [] as $v) {
                $exhibition->targetUsers()->create(['user_id' => $v]);
            }
        }

        if (is_array($request->input('contents'))) {
            foreach ($request->input('contents') ?? [] as $k => $v) {
                if ($v) {
                    $banner->contents()->create(['device' => $k]);
                }
            }
        }

        return response()->json($this->getOne($banner->id), 201);
    }

    /**
     * @OA\Get(
     *      path="/v1/exhibition/banner/{banner_id}",
     *      summary="배너 상세",
     *      description="배너 상세",
     *      operationId="exhibitionBannerShow",
     *      tags={"전시관리"},
     *      @OA\RequestBody(
     *          required=true,
     *          description=""
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ExhibitionBanner")
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
     * @param $banner_id
     * @return Collection
     */
    public function show($banner_id): Collection
    {
        return $this->getOne($banner_id);
    }

    /**
     * @OA\Patch(
     *      path="/v1/exhibition/banner/{banner_id}",
     *      summary="배너 수정",
     *      description="배너 수정",
     *      operationId="exhibitionBannerEdit",
     *      tags={"전시관리"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="exhibition_category_id", type="integer", example=5, description="전시관리 카테고리 고유번호(PK)"),
     *              @OA\Property(property="title", type="string", example="7월 광고배너", description="배너 제목"),
     *              @OA\Property(property="url", type="url", example="https://help.qpick.com/board/1", description="링크 URL"),
     *              @OA\Property(property="gaCode", type="url", example="27bf63c92ced856d1b931162a15383e3", description="구글 애널리틱스 트래킹 코드"),
     *              @OA\Property(property="memo", type="string", example="7월 회원가입 이벤트 광고배너", description="배너 설명"),
     *              @OA\Property(property="startedAt", type="string", format="date-time", description="전시기간 시작일자"),
     *              @OA\Property(property="endedAt", type="string", format="date-time", description="전시기간 종료일자"),
     *              @OA\Property(property="sort", type="integer", example=999, description="전시순서"),
     *              @OA\Property(property="visible", type="boolean", example=true, description="전시여부<br />true:노출<br />false:숨김"),
     *              @OA\Property(property="targetOpt", type="string", example="grade", description="전시 타겟설정<br />all:모든 회원<br />grade:회원구분<br />designate:특정회원"),
     *              @OA\Property(property="targetGrade[]", type="array of string", example="associate", description="타겟설정을 위한 회원등급, 타겟설정이 [회원구분]인 경우에 입력<br />associate:준회원<br />regular:정회원"),
     *              @OA\Property(property="targetUsers[]", type="array of integer", example="74", description="타겟설정을 위한 회원 고유번호(PK), 타겟설정이 [회원구분]인 경우에 입력"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/ExhibitionBanner")
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
     * @param int $banner_id
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, int $banner_id): JsonResponse
    {
        $banner = Banner::findOrFail($banner_id);
        $banner->update($request->all());
        $banner->exhibition->update($request->all());

        // Target User Update
        if (($request->input('target_opt') ?? $banner->exhibition->target_opt) == 'designate') {
            if ($request->input('target_users')) {
                ExhibitionTargetUser::where('exhibition_id', $banner->exhibition->id)
                    ->whereNotIn('user_id', $request->input('target_users'))
                    ->delete();

                foreach ($request->input('target_users') ?? [] as $v) {
                    ExhibitionTargetUser::updateOrCreate(
                        ['exhibition_id' => $banner->exhibition->id, 'user_id' => $v],
                        ['user_id' => $v]
                    );
                }
            }
        } else {
            $banner->targetUsers()->delete();
        }

        // Target Grade Update
        if (($request->input('target_opt') ?? $banner->exhibition->target_opt) == 'grade') {
            if ($request->input('target_grade')) {
                $banner->exhibition->update(['target_grade' => $request->input('target_grade')]);
            }
        } else {
            $banner->exhibition->update(['target_grade' => null]);
        }

        if (is_array($request->input('contents'))) {
            foreach ($request->input('contents') ?? [] as $k => $v) {
                if ($v) {
                    BannerDeviceContent::withTrashed()
                        ->updateOrCreate(['banner_id' => $banner_id, 'device' => $k], [])
                        ->restore();
                } else {
                    BannerDeviceContent::where('banner_id', $banner_id)
                        ->where('device', $k)
                        ->first()
                        ->delete();
                }
            }
        }

        return response()->json($this->getOne($banner_id), 201);
    }

    /**
     * @OA\delete(
     *      path="/v1/exhibition/banner/{banner_id}",
     *      summary="배너 삭제",
     *      description="배너 삭제",
     *      operationId="exhibitionBannerDelete",
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
     * @param int $banner_id
     * @return Response
     */
    public function destroy(int $banner_id): Response
    {
        Banner::findOrFail($banner_id)->delete();
        return response()->noContent();
    }

    protected function getOne(int $banner_id): Collection
    {
        $with = ['exhibition', 'exhibition.category', 'contents', 'creator'];
        return collect(Banner::with($with)->findOrFail($banner_id));
    }
}
