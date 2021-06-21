<?php

namespace App\Http\Controllers\Exhibitions;

use App\Http\Controllers\Controller;
use App\Http\Requests\Exhibitions\Categories\CreateRequest;
use App\Http\Requests\Exhibitions\Categories\UpdateRequest;
use App\Libraries\PaginationLibrary;
use App\Models\Exhibitions\ExhibitionCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Collection
     */
    public function index(Request $request): Collection
    {
        // init model
        $category = ExhibitionCategory::orderByDesc('id');

        // search condition
        if ($s = $request->input('enable')) {
            $category->where('enable', $s);
        }

        if ($s = $request->input('division')) {
            $category->where('division', $s);
        }

        // set pagination information
        $pagination = PaginationLibrary::set($request->input('page'), $category->count(), $request->input('per_page'));

        // get data from DB
        $data = $category->skip($pagination['skip'])->take($pagination['perPage'])->get();

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
        $res = ExhibitionCategory::create($request->all());
        return response()->json($this->getOne($res->id), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show(int $category_id)
    {
        return $this->getOne($category_id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $category_id
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, int $category_id): JsonResponse
    {
        ExhibitionCategory::findOrFail($category_id)->update($request->all());
        return response()->json($this->getOne($category_id), 201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $category_id
     * @return Response
     */
    public function destroy(int $category_id): Response
    {
        ExhibitionCategory::findOrFail($category_id)->delete();
        return response()->noContent();
    }

    protected function getOne(int $category_id)
    {
        return ExhibitionCategory::findOrFail($category_id);
    }
}
