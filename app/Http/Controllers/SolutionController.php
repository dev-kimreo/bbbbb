<?php

namespace App\Http\Controllers;

use App\Exceptions\QpickHttpException;
use App\Http\Requests\Solutions\IndexRequest;
use App\Http\Requests\Solutions\StoreRequest;
use App\Http\Requests\Solutions\UpdateRequest;
use App\Models\Solution;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class SolutionController extends Controller
{
    public string $exceptionEntity = "solution";

    /**
     * @OA\Post (
     *      path="/v1/solution",
     *      summary="솔루션 등록",
     *      description="새로운 솔루션을 등록합니다.",
     *      operationId="solutionCreate",
     *      tags={"솔루션"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"name"},
     *              @OA\Property(property="name", ref="#/components/schemas/Solution/properties/name")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Solution")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      )
     *  )
     */
    public function store(StoreRequest $request)
    {
        $solution = Solution::create($request->all());
        $solution->refresh();

        return $solution;
    }


    /**
     * @OA\Patch (
     *      path="/v1/solution/{solution_id}",
     *      summary="솔루션 수정",
     *      description="솔루션을 수정합니다.",
     *      operationId="solutionUpdate",
     *      tags={"솔루션"},
     *      @OA\RequestBody(
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="name", ref="#/components/schemas/Solution/properties/name"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Solution")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      )
     *  )
     */
    public function update(UpdateRequest $request, $solution_id): JsonResponse
    {
        Solution::findOrFail($solution_id)->update($request->all());

        return response()->json(Solution::find($solution_id), 201);
    }


    /**
     * @OA\Get (
     *      path="/v1/solution/{solution_id}",
     *      summary="솔루션 상세",
     *      description="솔루션의 상세정보",
     *      operationId="solutionShow",
     *      tags={"솔루션"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Solution")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      )
     *  )
     */
    public function show($solution_id)
    {
        return Solution::findOrFail($solution_id);
    }


    /**
     * @OA\Get (
     *      path="/v1/solution",
     *      summary="솔루션 목록",
     *      description="솔루션의 목록",
     *      operationId="solutionIndex",
     *      tags={"솔루션"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Solution")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      )
     *  )
     */
    public function index(IndexRequest $request)
    {
        return Solution::all();
    }


    /**
     * @OA\Delete (
     *      path="/v1/solution/{solution_id}",
     *      summary="솔루션 삭제",
     *      description="솔루션을 삭제합니다",
     *      operationId="solutionDestroy",
     *      tags={"솔루션"},
     *      @OA\Response(
     *          response=204,
     *          description="successfully",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      )
     *  )
     */
    public function destroy($solution_id): Response
    {
        Solution::findOrFail($solution_id)->delete();

        return response()->noContent();
    }

}
