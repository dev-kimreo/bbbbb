<?php

namespace App\Http\Controllers\Attach;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attaches\StoreRequest;
use App\Http\Resources\Attach\ComponentUploadImageResource;
use App\Libraries\PaginationLibrary;
use App\Models\Attach\ComponentUploadImage;
use App\Services\AttachService;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Image;

class ComponentUploadImageController extends Controller
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
        $model = ComponentUploadImage::query()->orderByDesc('id');

        // set pagination information
        $pagination = PaginationLibrary::set($request->input('page'), $model->count(), $request->input('per_page'));

        // get ids from DB
        $data = $model->skip($pagination['skip'])->take($pagination['perPage'])->get();

        // result
        $result = [
            'header' => $pagination ?? [],
            'list' => ComponentUploadImageResource::collection($data) ?? []
        ];

        return collect($result);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @param AttachService $attachService
     * @return JsonResponse
     */
    public function store(StoreRequest $request, AttachService $attachService): JsonResponse
    {
        // Getting width and height
        $image = Image::make($request->file('files'));

        // Create
        $attach = $attachService->create($request->file('files'))->refresh();
        $res = ComponentUploadImage::query()->create(
            [
                'attach_file_id' => $attach->getAttribute('id'),
                'user_id' => Auth::id(),
                'width' => $image->getWidth(),
                'height' => $image->getHeight()
            ]
        );

        // Move
        $attachService->move($res, [$attach->getAttribute('id')]);

        // Response
        return response()->json($this->getOne($res->getAttribute('id')), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param $id
     * @return Collection
     */
    public function show($id): Collection
    {
        return $this->getOne($id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy(int $id): Response
    {
        ComponentUploadImage::findOrFail($id)->delete();
        return response()->noContent();
    }

    protected function getOne(int $id): Collection
    {
        return collect(ComponentUploadImageResource::make(ComponentUploadImage::findOrFail($id)));
    }
}
