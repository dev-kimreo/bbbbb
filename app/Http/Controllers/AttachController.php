<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Cache;
use Storage;
use Artisan;

use App\Models\AttachFile;

use App\Http\Requests\Attaches\CreateAttachRequest;

use App\Exceptions\QpickHttpException;

use App\Libraries\CollectionLibrary;

use App\Services\AttachService;

/**
 * Class AttachController
 * @package App\Http\Controllers
 */
class AttachController extends Controller
{
    private $attach, $attachService;

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
     *                      property="files[]", type="array", description="",
     *                      @OA\Items(type="string", format="binary")
     *                  )
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully Created",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(type="object",
     *                  @OA\Property(property="url", type="string", example="http://local-api.qpicki.com/storage/temp/75bc15bf36e777ae26ad9be0b1745e08.jpg", description="url" ),
     *                  @OA\Property(property="orgName", type="string", example="자연환경.png", description="원본 파일명" ),
     *                  @OA\Property(property="extension", type="string", example="png", description="원본 확장자" ),
     *              ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed registered",
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="errors",
     *                  type="object",
     *                  @OA\Property(
     *                      property="statusCode",
     *                      type="object",
     *                      allOf={
     *                          @OA\Schema(
     *                              @OA\Property(property="100003", ref="#/components/schemas/RequestResponse/properties/100003"),
     *                              @OA\Property(property="100053", ref="#/components/schemas/RequestResponse/properties/100053"),
     *                              @OA\Property(property="100081", ref="#/components/schemas/RequestResponse/properties/100081"),
     *                              @OA\Property(property="100083", ref="#/components/schemas/RequestResponse/properties/100083"),
     *                          ),
     *                      }
     *                  )
     *              )
     *          )
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     */
    /**
     * @param Request $request
     * @return mixed
     */
    public function create(CreateAttachRequest $request)
    {
        $files = $request->file('files');
        $uploadFiles = [];
        $res = [];

        if (is_array($files) && count($files)) {
            foreach ($files as $img) {
                $uploadFiles[] = [
                    'path' => Storage::disk('public')->putFileAs($this->attachService->tempDir, $img, md5($img->getClientOriginalName() . microtime()) . "." . $img->getClientOriginalExtension()),
                    'orgName' => $img->getClientOriginalName()
                ];
            }
        } else {
            throw new QpickHttpException(422, 100001, '{files[]}');
        }

        foreach ($uploadFiles as $f) {
            $url = Storage::disk('public')->url($f['path']);
            $pathInfo = pathinfo($url);
            $path = pathInfo(str_replace(config('filesystems.disks.public.url') . '/', '', $url))['dirname'];

            // 저장
            $this->attach->server = 'public';
            $this->attach->attachable_type = $this->attachService->tempDir;
            $this->attach->attachable_id = 0;
            $this->attach->user_id = auth()->user() ? auth()->user()->id : 0;
            $this->attach->url = $url;
            $this->attach->path = $path;
            $this->attach->name = $pathInfo['basename'];
            $this->attach->org_name = $f['orgName'];
            $this->attach->save();

            $res[] = [
                'no' => $this->attach->id,
                'url' => $this->attach->url,
                'orgName' => $f['orgName'],
                'extension' => $pathInfo['extension']
            ];
        }

        return response()->json(CollectionLibrary::toCamelCase(collect($res)), 200);
    }

    /**
     * 단일 첨부파일 삭제
     * @param Request $request
     * @return bool|null
     */
    public function delete($id, Request $request)
    {
        $res = $this->attachService->delete([$request->id]);
        if (!$res) {
            throw new QpickHttpException(422, 10001);
        }

        return response()->json([
            'message' => __('common.deleted')
        ], 200);
    }


    public function test(Request $request)
    {
////        // temp 파일 리얼 type쪽으로 이동
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
