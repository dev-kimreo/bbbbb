<?php

namespace App\Http\Controllers\Attach;

use App\Exceptions\QpickHttpException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attaches\StoreRequest;
use App\Http\Requests\Attaches\UpdateRequest;
use App\Models\Attach\AttachFile;
use App\Services\AttachService;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class AttachController
 * @package App\Http\Controllers
 */
class AttachController extends Controller
{
    private AttachService $attachService;
    private AttachFile $attach;
    public string $exceptionEntity = "attach";

    public function __construct(AttachFile $attach, AttachService $attachService)
    {
        $this->attach = $attach;
        $this->attachService = $attachService;
    }

    /**
     * @OA\Post(
     *      path="/v1/attach",
     *      summary="첨부파일 임시 저장",
     *      description="첨부파일 임시 저장",
     *      operationId="attachFileCreate",
     *      tags={"첨부파일"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="files", type="string", description="", format="binary"
     *                  )
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="created",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/AttachFile"
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="bad request"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $attach = $this->attachService->create($request->file('files'))->refresh();
        return response()->json(collect($attach), 201);
    }


    /**
     * @OA\Schema (
     *      schema="attachModify",
     *      required={"type", "typeId"},
     *      @OA\Property(property="type", type="string", example="post", description="사용처" ),
     *      @OA\Property(property="typeId", type="integer", example=1, description="사용처의 고유번호" ),
     *      @OA\Property(property="thumbnail", type="integer", example=1, default=0, description="섬네일로 사용 여부, 1:사용" )
     *  )
     *
     * @OA\Patch(
     *      path="/v1/attach/{id}",
     *      summary="첨부파일 수정",
     *      description="첨부파일 수정",
     *      operationId="attachFileModify",
     *      tags={"첨부파일"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/attachModify"
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="modified",
     *          @OA\JsonContent(
     *              ref="#/components/schemas/AttachFile"
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="bad request"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="forbidden"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     *
     * @param $id
     * @param UpdateRequest $request
     * @return JsonResponse
     * @throws QpickHttpException
     */
    public function update(UpdateRequest $request, $id): JsonResponse
    {
        // Attach find
        $attachCollect = $this->attach->where(['id' => $id, 'attachable_type' => 'temp'])->first();
        if (!$attachCollect) {
            throw new QpickHttpException(404, 'common.not_found');
        }

        if (!auth()->user()->can('update', $attachCollect)) {
            throw new QpickHttpException(403, 'common.unauthorized');
        }

        // type check
        $typeModel = Relation::getMorphedModel($request->input('type'));
        if (!$typeModel) {
            throw new QpickHttpException(422, 'common.not_found', 'type');
        }
        $typeModel = '\\' . $typeModel;
        $typeCollect = $typeModel::find($request->input('type_id'));

        if ($request->has('thumbnail') && $request->input('thumbnail')) {
            $typeCollect = $typeCollect->thumbnail()->firstOrCreate();
        }

        if (!$typeCollect) {
            throw new QpickHttpException(422, 'common.not_found', 'type_id');
        }

        // check use upload file
        $this->attachService->checkAttachableModel($typeCollect);

        // check upload file limit count
        $this->attachService->checkUnderUploadLimit($typeCollect);

        // type Move
        $this->attachService->move($typeCollect, [$id]);

        // Attach Collection Refresh
        $attachCollect->refresh();

        // return
        return response()->json($attachCollect, 201);
    }


    /**
     * @OA\Delete(
     *      path="/v1/attach/{id}",
     *      summary="첨부파일 삭제",
     *      description="첨부파일 삭제",
     *      operationId="replyDelete",
     *      tags={"첨부파일"},
     *      @OA\Response(
     *          response=204,
     *          description="deleted."
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="forbidden"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     */

    /**
     * 단일 첨부파일 삭제
     * @param $id
     * @return Response
     * @throws QpickHttpException
     */
    public function delete($id): Response
    {
        $this->attachService->delete([$id]);

        return response()->noContent();
    }

    public function test(Request $request)
    {
////        // temp 파일 리얼 type 쪽으로 이동
//        $this->move('board', 125, [
//            "http://local-api.qpicki.com/storage/temp/dfd62b726f09810c958bf7df0e60df5e.jpg",
//        ]);

        // 전체 삭제
//        $this->delete('board', 255);

        // 특정 파일만 삭제
//        $this->delete('board', 125, [
//            "http://local-api.qpicki.com/storage/board/07d/000/000/ba430f8a4ebbb7ed9eebbec7333cad2e.jpg",
//            "http://local-api.qpicki.com/storage/board/07d/000/000/d2cd4bff4d0694690908c8f59919475d.png"
//        ]);
        //
    }
}
