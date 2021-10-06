<?php

namespace App\Services;

use App\Exceptions\QpickHttpException;
use App\Models\AttachFile;
use Auth;
use Illuminate\Http\UploadedFile;
use Storage;

class AttachService
{
    private AttachFile $attach;

    public string $tempDir = 'temp';    // 임시 파일 저장 디렉토리
    private int $hexLength = 9;         // hex 길이 16진수 9승 687억개 가능
    private int $levelDepth = 3;        // 폴더 구분 3자리씩 최대 16의 3승 4096개
    private array $path = [];
    protected array $allowType = ['temp', 'board', 'post'];

    /**
     * PostService constructor.
     * @param AttachFile $attach
     */
    public function __construct(AttachFile $attach)
    {
        $this->attach = $attach;
    }

    /**
     * @param UploadedFile $file
     * @return AttachFile
     */
    public function create(UploadedFile $file): AttachFile
    {
        return $this->createDbRecord($this->uploadFile($file), $file->getClientOriginalName());
    }

    /**
     * @param UploadedFile $file
     * @return string
     */
    protected function uploadFile(UploadedFile $file): string
    {
        $fileName = md5($file->getClientOriginalName() . microtime()) . "." . $file->getClientOriginalExtension();
        return Storage::disk('public')->putFileAs($this->tempDir, $file, $fileName);
    }

    /**
     * @param string $filepath
     * @param string $orgName
     * @return AttachFile
     */
    protected function createDbRecord(string $filepath, string $orgName): AttachFile
    {
        $url = Storage::disk('public')->url($filepath);
        $name = pathinfo($url, PATHINFO_BASENAME);
        $path = pathinfo(str_replace(config('filesystems.disks.public.url') . '/', '', $url), PATHINFO_DIRNAME);

        return $this->attach->create(
            [
                'server' => 'public',
                'attachable_type' => $this->tempDir,
                'attachable_id' => 0,
                'user_id' => Auth::user() ? Auth::id() : 0,
                'url' => $url,
                'path' => $path,
                'name' => $name,
                'org_name' => $orgName
            ]
        );
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

        $hexName = str_pad(dechex($alias), $this->hexLength, '0', STR_PAD_LEFT);

        for ($i = -$this->levelDepth; abs($i) <= $this->hexLength; $i -= $this->levelDepth) {
            $this->path[] = substr($hexName, $i, $this->levelDepth);
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
                    $dir = $alias . '/' . implode('/', $this->path) . '/' . $pathInfo['basename'];
                    Storage::disk($disk)->put($dir, $orgImg);

                    // 첨부파일 데이터 수정
                    $url = Storage::disk($disk)->url($dir);
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
        return $diskServer[hexdec($this->path[0]) % count($diskServer)];
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
