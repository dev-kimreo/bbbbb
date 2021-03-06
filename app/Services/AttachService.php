<?php

namespace App\Services;

use App\Exceptions\QpickHttpException;
use App\Models\Attach\AttachFile;
use Auth;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Relations\Relation;
use Storage;
use Image;

class AttachService
{
    protected AttachFile $attach;
    protected static string $storageDisk = 'public';

    public string $tempDir = 'temp';    // 임시 파일 저장 디렉토리
    public string $thumbDir = 'thumb';    // 썸네일 저장 디렉토리
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

    public static function setStorageDisk(string $storageDisk = 'public')
    {
        self::$storageDisk = $storageDisk;
    }

    /**
     * @param UploadedFile $file
     * @return AttachFile
     */
    public function create(UploadedFile $file): AttachFile
    {
        // Upload to storage
        $fileName = $this->getFileNameByHash($file->getClientOriginalName(), $file->getClientOriginalExtension());
        $filePath = Storage::disk(self::$storageDisk)->putFileAs($this->tempDir, $file, $fileName);

        // Make a record on database
        $model = $this->createDbRecord($filePath, $file->getClientOriginalName(), $file->getSize());

        // Make Thumbnail
        $this->makeThumb($model, $file);

        // Return
        return $model->fresh();
    }

    /**
     * @param AttachFile $parent
     * @param UploadedFile $file
     * @param int $fitSize
     * @return bool
     */
    protected function makeThumb(AttachFile $parent, UploadedFile $file, int $fitSize = 300): bool
    {
        // Resize the uploaded image
        $resizedImage = Image::make($file)->fit($fitSize)->encode('jpg', 40);
        $encodedImage = $resizedImage->getEncoded();

        // Upload to storage
        $fileName = preg_replace('/\.(.*)$/', '.thumb.$1', $file->getClientOriginalName());
        $hashName = $this->getFileNameByHash($fileName . '*', $file->getClientOriginalExtension());
        $filePath = $this->thumbDir . '/' . $hashName;
        Storage::disk(self::$storageDisk)->put($filePath, $encodedImage);

        // Make a record on database
        $this->createDbRecord(
            $filePath,
            $fileName,
            strlen($encodedImage),
            collect(Relation::morphMap())->search(AttachFile::class),
            $parent->getAttribute('id')
        );

        // Return
        return true;
    }

    /**
     * @param $fileName
     * @param $ext
     * @return string
     */
    protected function getFileNameByHash($fileName, $ext): string
    {
        return md5($fileName . microtime()) . "." . $ext;
    }

    /**
     * @param string $path
     * @param File|UploadedFile|string $file
     * @param string $fileName
     * @return string
     */
    protected function uploadFile(string $path, $file, string $fileName): string
    {
        return Storage::disk(self::$storageDisk)->putFileAs($path, $file, $fileName);
    }

    /**
     * @param string $filepath
     * @param string $orgName
     * @param int $size
     * @param string $attachable_type
     * @param int $attachable_id
     * @return AttachFile
     */
    protected function createDbRecord(
        string $filepath,
        string $orgName,
        int $size,
        string $attachable_type = 'temp',
        int $attachable_id = 0
    ): AttachFile {
        $url = Storage::disk(self::$storageDisk)->url($filepath);
        $name = pathinfo($url, PATHINFO_BASENAME);

        $data = [
            'server' => self::$storageDisk,
            'attachable_type' => $attachable_type,
            'attachable_id' => $attachable_id,
            'user_id' => Auth::user() ? Auth::id() : 0,
            'url' => $url,
            'path' => $filepath,
            'name' => $name,
            'org_name' => $orgName,
            'size' => $size
        ];

        return $this->attach->create($data)->fresh();
    }

    public function move($collect, array $nos, $etc = []): bool
    {
        if (!$collect) {
            return false;
        }

        if (!$alias = $collect->getMorphClass()) {
            return false;
        }

        // 저장경로
        $hexName = str_pad(dechex($nos[0]), $this->hexLength, '0', STR_PAD_LEFT);

        for ($i = -$this->levelDepth; abs($i) <= $this->hexLength; $i -= $this->levelDepth) {
            $this->path[] = substr($hexName, $i, $this->levelDepth);
        }

        $dir = $alias . '/' . implode('/', $this->path);

        // 저장경로 디렉토리 생성
        Storage::disk(self::$storageDisk)->makeDirectory($dir);

        // 파일 이동
        if ($attach = $this->attach->tempType()->whereIn('id', $nos)->get()) {
            foreach ($attach as $v) {
                if (!Storage::disk(self::$storageDisk)->exists($v->path)) {
                    continue;
                }

                $path = $dir . '/' . $v->name;
                $orgImg = Storage::disk(self::$storageDisk)->get($v->path);
                Storage::disk(self::$storageDisk)->put($path, $orgImg);

                // 첨부파일 데이터 수정
                $attachModel = clone $v;
                $attachModel->server = self::$storageDisk;
                $attachModel->attachable_type = $alias;
                $attachModel->attachable_id = $collect->id;
                $attachModel->url = Storage::disk(self::$storageDisk)->url($path);
                $attachModel->path = $path;
                $attachModel->etc = $etc;
                $attachModel->update();

                // 원본 삭제
                Storage::disk(self::$storageDisk)->delete($v->path);
            }
        }

        return true;
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

    public function checkUnderUploadCountLimit($collect): bool
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

        if (method_exists($collect, 'getAttachFileLimit')) {
            $uploadLimit = $collect->getAttachFileLimit();
        } else {
            $uploadLimit = 1;
        }

        if ($uploadLimit <= $uploadCount) {
            throw new QpickHttpException(422, 'attach.over.limit');
        }

        return true;
    }
}
