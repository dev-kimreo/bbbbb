<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Libraries\CollectionLibrary;
use App\Models\UserLinkedSolution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserLinkedSolutionController extends Controller
{
    /**
     * @param Request $req
     * @return JsonResponse
     */
    public function store(int $user_id, Request $req): JsonResponse
    {
        $params = array_merge($req->toArray(), ['user_id' => $user_id]);
        $data = UserLinkedSolution::create($params);
        return response()->json(CollectionLibrary::toCamelCase(collect($data)), 201);
    }

    /**
     * @param int $user_id
     * @return Response
     */
    public function destroy(int $user_id, int $solution_id): Response
    {
        UserLinkedSolution::findOrFail($solution_id)->delete();
        return response()->noContent();
    }
}
