<?php

namespace App\Services;

use App\Exceptions\QpickHttpException;
use App\Models\AttachFile;
use Auth;
use Storage;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;

class AttachService
{
    private AttachFile $attach;

    public $tempDir = 'temp';       // 임시 파일 저장 디렉토리
    private $hexLength = 9;          // hex 길이 16진수 9승 687억개 가능
    private $levelDepth = 3;         // 폴더 구분 3자리씩 최대 16의 3승 4096개
    private $hexName = null;         // hex
    private $path = [];

    protected $allowType = ['temp', 'board', 'post'];

    /**
     * PostService constructor.
     * @param AttachFile $attach
     */
    public function __construct(AttachFile $attach)
    {
        $this->attach = $attach;
    }


    public function move($collect, array $nos, $etc = []): bool
    {
        if (!$collect) {
            return false;
        }

        $alias = $collect->getMorphClass();
        if (!$alias) {
            return false;
        }

        $this->hexName = str_pad(dechex($alias), $this->hexLength, '0', STR_PAD_LEFT);

        for ($i = -$this->levelDepth; abs($i) <= $this->hexLength; $i -= $this->levelDepth) {
            $this->path[] = substr($this->hexName, $i, $this->levelDepth);
        }

        $disk = $this->funcGetServer();

        if (is_array($nos) && count($nos)) {
            $attach = $this->attach->tempType()->whereIn('id', $nos)->get();

            if ($attach) {
                foreach ($attach as $arr) {
                    $pathInfo = pathinfo($arr->url);

                    if (!Storage::disk('public')->exists($this->tempDir . '/' . $pathInfo['basename'])) {
                        continue;
                    }

                    $orgImg = Storage::disk('public')->get($this->tempDir . '/' . $pathInfo['basename']);

                    // 폴더 존재여부
                    if (!Storage::disk($disk)->exists($alias . '/' . implode('/', $this->path))) {
                        Storage::disk($disk)->makeDirectory($alias . '/' . implode('/', $this->path));
                    }

                    // 이동
                    Storage::disk($disk)->put($alias . '/' . implode('/', $this->path) . '/' . $pathInfo['basename'], $orgImg);

                    // 첨부파일 데이터 수정
                    $url = Storage::disk($disk)->url($alias . '/' . implode('/', $this->path) . '/' . $pathInfo['basename']);
                    $pathInfo = pathinfo($url);
                    $path = pathInfo(str_replace(config('filesystems.disks.' . $disk . '.url') . '/', '', $url))['dirname'];

                    $attachModel = $arr;
                    $attachModel->server = $disk;
                    $attachModel->attachable_type = $alias;
                    $attachModel->attachable_id = $collect->id;
                    $attachModel->url = $url;
                    $attachModel->path = $path;
                    $attachModel->etc = $etc;
                    $attachModel->update();

                    // 원본 삭제
                    Storage::disk('public')->delete($this->tempDir . '/' . $pathInfo['basename']);
                }
            }
        }

        return true;
    }

    protected function funcGetServer()
    {
        $diskServer = config('filesystems.custom.servers');
        $curServer = $diskServer[hexdec($this->path[0]) % count($diskServer)];

        return $curServer;
    }

    public function delete(array $no = []): bool
    {
        if (!count($no)) {
            return false;
        }

        foreach ($no as $n) {
            $attachFile = $this->attach->where(['id' => $n])->first();
            // 파일이 존재하지 않을 경우
            if (!$attachFile) {
                throw new QpickHttpException(422, 'common.not_found');
            }

            if (!auth()->user()->can('delete', $attachFile)) {
                throw new QpickHttpException(403, 'common.unauthorized');
            }

            Storage::disk($attachFile->server)->delete($attachFile->path . '/' . $attachFile->name);
            $attachFile->delete();
        }

        return true;
    }

    public function checkAttachableModel($collect): bool
    {
        if (method_exists($collect, 'checkAttachableModel') && !$collect->checkAttachableModel()) {
            throw new QpickHttpException(403, 'attach.disable.upload');
        }

        return true;
    }

    public function checkUnderUploadLimit($collect): bool
    {
        $alias = $collect->getMorphClass();

        if (!$alias) {
            throw new QpickHttpException(422, 'common.not_found');
        }

        $uploadCount = $this->attach->where([
            'attachable_type' => $alias,
            'attachable_id' => $collect->id,
            'user_id' => Auth::id()
        ])->count();

        if ($collect->getAttachFileLimit() <= $uploadCount) {
            throw new QpickHttpException(422, 'attach.over.limit');
        }

        return true;
    }
}
