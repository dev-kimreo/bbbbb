<?php

namespace App\Http\Controllers;

use App\Exceptions\QpickHttpException;
use App\Http\Requests\Inquiries\Answers\DestroyRequest;
use App\Http\Requests\Inquiries\Answers\ShowRequest;
use App\Http\Requests\Inquiries\Answers\StoreRequest;
use App\Http\Requests\Inquiries\Answers\UpdateRequest;
use App\Models\Inquiry;
use App\Models\InquiryAnswer;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;


class InquiryAnswerController extends Controller
{
    private InquiryAnswer $answer;

    public function __construct(InquiryAnswer $answer)
    {
        $this->answer = $answer;
    }

    /**
     * @OA\Post (
     *      path="/v1/inquiry/{id]/answer",
     *      summary="1:1문의 답변 등록",
     *      description="1:1문의에 새로운 답변을 등록합니다",
     *      operationId="inquiryAnswerCreate",
     *      tags={"1:1문의"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"answer"},
     *              @OA\Property(
     *                  property="answer", type="string", description="답변내용",
     *                  example="더 좋은 큐픽 서비스가 될 수 있도록 최선을 다하겠습니다."
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/InquiryAnswer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      )
     *  )
     */

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @param int $inquiryId
     * @return JsonResponse
     * @throws QpickHttpException
     */
    public function store(StoreRequest $request, int $inquiryId): JsonResponse
    {
        // check exists inquiry
        Inquiry::findOrFail($inquiryId);

        // check duplicated
        if(InquiryAnswer::where('inquiry_id', $inquiryId)->first()) {
            throw new QpickHttpException(409, 'inquiry.answer.disable.already_exists');
        }

        // store
        $answer = $this->answer;
        $answer->user_id = Auth::id();
        $answer->inquiry_id = $inquiryId;
        $answer->answer = $request->input('answer');
        $answer->save();

        $answer->inquiry->status = Inquiry::$status['answered'];
        $answer->inquiry->save();

        // response
        $data = $this->getOne($answer->inquiry_id);
        return response()->json(collect($data), 201);
    }

    /**
     * @OA\Get (
     *      path="/v1/inquiry/{id}/answer",
     *      summary="1:1문의 답변 조회",
     *      description="1:1문의에 작성된 답변을 조회합니다. 다만 실제 서비스 구현시에는 1:1문의 상세 API 사용을 권장합니다.",
     *      operationId="inquiryAnswerGetInfo",
     *      tags={"1:1문의"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={}
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              allOf={
     *                  @OA\Schema(ref="#/components/schemas/InquiryAnswer"),
     *                  @OA\Schema(
     *                      @OA\Property(
     *                          property="inquiry",
     *                          ref="#/components/schemas/Inquiry"
     *                      )
     *                  )
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="not found"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      )
     *  )
     */

    /**
     * Display the specified resource.
     *
     * @param ShowRequest $request
     * @param int $inquiryId
     * @return Collection
     */
    public function show(ShowRequest $request, int $inquiryId): Collection
    {
        return collect($this->getOne($inquiryId));
    }

    /**
     * @OA\Patch (
     *      path="/v1/inquiry/{id]/answer",
     *      summary="1:1문의 답변 수정",
     *      description="1:1문의에 등록되었던 답변을 수정합니다",
     *      operationId="inquiryAnswerModify",
     *      tags={"1:1문의"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"answer"},
     *              @OA\Property(
     *                  property="answer", type="string", description="답변내용",
     *                  example="더 좋은 큐픽 서비스가 될 수 있도록 최선을 다하겠습니다."
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/InquiryAnswer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="not found"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      )
     *  )
     */

    /**
     * Update the specified resource in storage.
     * !Warning! The router for this method is customized.
     *
     * @param UpdateRequest $request
     * @param int $inquiryId
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, int $inquiryId): JsonResponse
    {
        // getting original data
        Inquiry::findOrFail($inquiryId);
        $answer = $this->answer->where('inquiry_id', $inquiryId)->firstOrFail();

        // update
        $answer->answer = $request->input('answer', $answer->answer);
        $answer->saveOrFail();

        // response
        $data = $this->getOne($inquiryId);
        return response()->json(collect($data), 201);
    }

    /**
     * @OA\Delete(
     *      path="/v1/inquiry/{id]/answer",
     *      summary="1:1문의 답변 삭제",
     *      description="기존에 등록된 1:1문의의 답변을 삭제합니다",
     *      operationId="inquiryAnswerDelete",
     *      tags={"1:1문의"},
     *      @OA\RequestBody(
     *          required=true,
     *          description=""
     *      ),
     *      @OA\Response(
     *          response=204,
     *          description="successfully"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="not found"
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     */

    /**
     * Remove the specified resource from storage.
     * !Warning! The router for this method is customized.
     *
     * @param DestroyRequest $request
     * @param int $inquiryId
     * @return Response
     */
    public function destroy(DestroyRequest $request, int $inquiryId): Response
    {
        // find
        $inquiry = Inquiry::findOrFail($inquiryId);
        $answer = $inquiry->answer;

        // delete
        InquiryAnswer::findOrFail(@$answer->id)->destroy($answer->id);

        // change status
        $inquiry->status = $inquiry->assignee_id? Inquiry::$status['answering']: Inquiry::$status['waiting'];
        $inquiry->save();

        // response
        return response()->noContent();
    }

    protected function getOne(int $inquiryId)
    {
        return $this->answer->where('inquiry_id', $inquiryId)->with(['user', 'inquiry'])->firstOrFail();
    }
}
