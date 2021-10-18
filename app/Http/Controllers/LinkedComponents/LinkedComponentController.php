<?php

namespace App\Http\Controllers\LinkedComponents;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\LinkedComponents\IndexRequest;
use App\Libraries\CollectionLibrary;
use App\Libraries\PaginationLibrary;
use App\Models\EditablePages\EditablePage;
use App\Models\LinkedComponents\LinkedComponentGroup;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class LinkedComponentController extends Controller
{
    /**
     * @param IndexRequest $request
     * @param int $themeId
     * @param int $editablePageId
     * @return \Illuminate\Support\Collection
     */
    public function index(IndexRequest $request, int $themeId, int $editablePageId)
    {
    }


}
