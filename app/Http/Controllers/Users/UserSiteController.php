<?php

namespace App\Http\Controllers\Users;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\UserSites\DestroyRequest;
use App\Http\Requests\Users\UserSites\IndexRequest;
use App\Http\Requests\Users\UserSites\StoreRequest;
use App\Http\Requests\Users\UserSites\UpdateRequest;
use App\Libraries\PaginationLibrary;
use App\Models\Users\UserSite;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class UserSiteController extends Controller
{
    /**
     * @param IndexRequest $req
     * @param int $user_id
     * @return Collection
     */
    public function index(IndexRequest $req, int $user_id): Collection
    {
        // init model
        $model = UserSite::query()
            ->with('userSolution.solution')
            ->orderByDesc('id');

        // query
        $model->where('user_id', $user_id);

        if ($v = $req->input('solution_id')) {
            $model->whereHas('userSolution', function ($query) use ($v) {
                return $query->where('solution_id', $v);
            });
        }

        if ($v = $req->input('user_solution_id')) {
            $model->where('user_solution_id', $v);
        }

        // set pagination information
        $pagination = PaginationLibrary::set($req->input('page'), $model->count(), $req->input('per_page'));

        // get data
        $data = $model->skip($pagination['skip'])->take($pagination['perPage'])->get();

        // result
        $result = [
            'header' => $pagination ?? [],
            'list' => $data ?? []
        ];

        return collect($result);
    }

    /**
     * @param Request $req
     * @param int $user_id
     * @return JsonResponse
     */
    public function store(StoreRequest $req, int $user_id): JsonResponse
    {
        $params = array_merge($req->toArray(), ['user_id' => $user_id]);
        $data = UserSite::query()->create($params);
        return response()->json(collect($this->getOne($data->id)), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param int $user_id
     * @param int $site_id
     * @return JsonResponse
     */
    public function show(int $user_id, int $site_id): JsonResponse
    {
        return response()->json(collect($this->getOne($site_id)), 201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $req
     * @param int $user_id
     * @param int $site_id
     * @return JsonResponse
     */
    public function update(UpdateRequest $req, int $user_id, int $site_id): JsonResponse
    {
        UserSite::query()->findOrfail($site_id)->update($req->toArray());
        return response()->json(collect($this->getOne($site_id)), 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $user_id
     * @param int $site_id
     * @return Response
     */
    public function destroy(DestroyRequest $req, int $user_id, int $site_id): Response
    {
        UserSite::query()->findOrFail($site_id)->delete();
        return response()->noContent();
    }


    protected function getOne($id): Collection
    {
        return collect(UserSite::query()->with('userSolution.solution')->find($id));
    }
}
