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
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PopupController extends Controller
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

        if (is_array($s = $request->input('target_opt'))) {
            $popup->whereHas('exhibition', function ($q) use ($s) {
                $q->whereJsonContains('target_opt', $s);
            });
        }

        if (strlen($s = $request->input('visible'))) {
            $popup->whereHas('exhibition', function ($q) use ($s) {
                $q->where('visible', $s);
            });
        }

        // set pagination information
        $pagination = PaginationLibrary::set($request->input('page'), $popup->count(), $request->input('per_page'));

        // get data from DB
        $data = $popup->skip($pagination['skip'])->take($pagination['perPage'])->get();

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
     * Display the specified resource.
     *
     * @param $popup_id
     * @return Collection
     */
    public function show($popup_id): Collection
    {
        return $this->getOne($popup_id);
    }

    /**
     * Update the specified resource in storage.
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
     * Remove the specified resource from storage.
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
