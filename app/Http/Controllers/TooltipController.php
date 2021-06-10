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
