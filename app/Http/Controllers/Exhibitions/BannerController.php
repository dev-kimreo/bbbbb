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
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
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

        if ($s = $request->input('device')) {
            $func = in_array($s, ['mobile', 'both'])? 'whereHas': 'whereDoesntHave';
            $banner->$func('contents', function ($q) {
                $q->where('device', 'mobile');
            });

            $func = in_array($s, ['pc', 'both'])? 'whereHas': 'whereDoesntHave';
            $banner->$func('contents', function ($q) {
                $q->where('device', 'pc');
            });
        }

        if (is_array($s = $request->input('target_opt'))) {
            $banner->whereHas('exhibition', function ($q) use ($s) {
                $q->whereJsonContains('target_opt', $s);
            });
        }

        if (strlen($s = $request->input('visible'))) {
            $banner->whereHas('exhibition', function ($q) use ($s) {
                $q->where('visible', $s);
            });
        }

        // set pagination information
        $pagination = PaginationLibrary::set($request->input('page'), $banner->count(), $request->input('per_page'));

        // get data from DB
        $data = $banner->skip($pagination['skip'])->take($pagination['perPage'])->get();

        $data->each(function(&$v, $k) {
            $v->setHidden(['contents']);
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
     * Store a newly created resource in storage.
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
                if($v) {
                    $banner->contents()->create(['device' => $k]);
                }
            }
        }

        return response()->json($this->getOne($banner->id), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param $banner_id
     * @return Collection
     */
    public function show($banner_id): Collection
    {
        return $this->getOne($banner_id);
    }

    /**
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
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
