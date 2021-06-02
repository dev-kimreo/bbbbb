<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\UserLinkedSolution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserLinkedSolutionController extends Controller
{
    /**
     * @param Request $req
     * @param int $user_id
     * @return JsonResponse
     */
    public function store(Request $req, int $user_id): JsonResponse
    {
        $params = array_merge($req->toArray(), ['user_id' => $user_id]);
        $data = UserLinkedSolution::create($params);
        return response()->json(collect($data), 201);
    }

    /**
     * @param int $user_id
     * @param int $solution_id
     * @return Response
     */
    public function destroy(int $user_id, int $solution_id): Response
    {
        UserLinkedSolution::findOrFail($solution_id)->delete();
        return response()->noContent();
    }
}
