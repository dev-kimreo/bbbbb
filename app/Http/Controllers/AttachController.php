<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Closure;
use Cache;
use Storage;
use Artisan;

use App\Models\AttachFile;

use App\Http\Requests\Attaches\CreateAttachRequest;

use App\Exceptions\QpickHttpException;

use App\Libraries\PaginationLibrary;
use App\Libraries\CollectionLibrary;

/**
 * Class AttachController
 * @package App\Http\Controllers
 */
class AttachController extends Controller
{
    public $tempDir = 'temp';       // 임시 파일 저장 디렉토리
    public $hexLength = 9;          // hex 길이 16진수 9승 687억개 가능
    public $levelDepth = 3;         // 폴더 구분 3자리씩 최대 16의 3승 4096개
    public $hexName = null;         // hex
    public $path = [];

    protected $allowType = ['temp', 'board', 'post'];

    public function __construct(AttachFile $attachFile)
    {
        $this->attach = $attachFile;
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
                    'path' => Storage::disk('public')->putFileAs($this->tempDir, $img, md5($img->getClientOriginalName() . microtime()) . "." . $img->getClientOriginalExtension()),
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
            $this->attach->type = $this->tempDir;
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


    public function move($type, $typeId, array $nos, $etc = [])
    {
        if ($typeId <= 0 || !in_array($type, $this->allowType)) {
            return false;
        }

        $this->hexName = str_pad(dechex($typeId), $this->hexLength, '0', STR_PAD_LEFT);

        for ($i = -$this->levelDepth; abs($i) <= $this->hexLength; $i -= $this->levelDepth) {
            $this->path[] = substr($this->hexName, $i, $this->levelDepth);
        }

        $disk = $this->funcGetServer();

        if (is_array($nos) && count($nos)) {
            $attach = $this->attach->where('type', 'temp')->whereIn('id', $nos)->get();
            if ($attach) {
                foreach ($attach as $arr) {
                    $pathInfo = pathinfo($arr->url);

                    if (!Storage::disk('public')->exists($this->tempDir . '/' . $pathInfo['basename'])) {
                        continue;
                    }

                    $orgImg = Storage::disk('public')->get($this->tempDir . '/' . $pathInfo['basename']);

                    // 폴더 존재여부
                    if (!Storage::disk($disk)->exists($type . '/' . implode('/', $this->path))) {
                        Storage::disk($disk)->makeDirectory($type . '/' . implode('/', $this->path));
                    }

                    // 이동
                    Storage::disk($disk)->put($type . '/' . implode('/', $this->path) . '/' . $pathInfo['basename'], $orgImg);

                    // 첨부파일 데이터 수정
                    $url = Storage::disk($disk)->url($type . '/' . implode('/', $this->path) . '/' . $pathInfo['basename']);
                    $pathInfo = pathinfo($url);
                    $path = pathInfo(str_replace(config('filesystems.disks.' . $disk . '.url') . '/', '', $url))['dirname'];

                    $attachModel = $arr;
                    $attachModel->server = $disk;
                    $attachModel->type = $type;
                    $attachModel->type_id = $typeId;
                    $attachModel->url = $url;
                    $attachModel->path = $path;
                    $attachModel->etc = $etc;
                    $attachModel->update();

                    // 원본 삭제
                    Storage::disk('public')->delete($this->tempDir . '/' . $pathInfo['basename']);
                }
            }
        }
    }


    /**
     * 단일 첨부파일 삭제
     * @param Request $request
     * @return bool|null
     */
    public function delete($id, Request $request)
    {
        $res = $this->directDelete([$request->id]);
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


    public function directDelete(array $no = [])
    {
        if (!count($no)) {
            return false;
        }

        foreach ($no as $n) {
            $attachFile = $this->attach->where(['id' => $n, 'user_id' => auth()->user()->id])->first();
            // 파일이 존재하지 않을 경우
            if (!$attachFile) {
                continue; //'파일 존재하지 않거나 내께 아니야'
            }

            Storage::disk($attachFile->server)->delete($attachFile->path . '/' . $attachFile->name);
            $attachFile->delete();
        }

        return true;
    }

    public function funcGetServer()
    {
        $diskServer = config('filesystems.custom.servers');
        $curServer = $diskServer[hexdec($this->path[0]) % count($diskServer)];

        return $curServer;
    }


    public function funcDelete($type, $typeId, array $no = [])
    {
        if (!$typeId) {
            return false;
        }

        // 특정 파일만 삭제
        if (is_array($no) && count($no)) {
            foreach ($no as $n) {
                echo $n . '--' . $type;
                $attachFile = $this->attach->where(['id' => $n, 'type' => $type, 'type_id' => $typeId, 'user_id' => auth()->user()->id])->first();

                // 파일이 존재하지 않을 경우
                if (!$attachFile) {
                    return '파일 존재하지 않아';
                }

                Storage::disk($attachFile->server)->delete($attachFile->path . '/' . $attachFile->name);
                $attachFile->delete();
            }
        } // 전부 삭제
        else {
            // 존재 유무 체크
            $AttachWhereModel = $this->attach->where(['type' => $type, 'type_id' => $typeId]);

            if ($attachFile = $AttachWhereModel->first()) {
                Storage::disk($attachFile->server)->deleteDirectory($attachFile->path);
                $this->attach->where(['type' => $type, 'type_id' => $typeId])->delete();
            }
        }

    }


}
