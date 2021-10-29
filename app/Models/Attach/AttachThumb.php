<?php

namespace App\Models\Attach;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 *  @OA\Schema(
 *      schema="AttachThumb",
 *      @OA\Property(property="server", type="string", example="public", description="업로드된 서버" ),
 *      @OA\Property(
 *          property="url",
 *          type="string",
 *          example="https://qpicki.com/storage/thumb/28dcce820b3464c827a2746678cce8dc.jpg",
 *          description="파일 url"
 *      ),
 *      @OA\Property(property="path", type="string", example="temp", description="파일 경로" ),
 *      @OA\Property(property="name", type="string", example="123asfd12ju4121.jpg", description="파일 이름" ),
 *      @OA\Property(property="orgName", type="string", example="홍길동.jpg", description="파일 원래 이름" ),
 *      @OA\Property(property="size", type="integer", example="8402", description="파일의 크기(byte)" )
 *  )
 *
 *  @OA\Schema(
 *      schema="AttachThumbSimply",
 *      @OA\Property(property="id", type="integer", example=1, description="업로드 파일 고유 번호" ),
 *      @OA\Property(
 *          property="url",
 *          type="string",
 *          example="https://qpicki.com/storage/temp/123asfd12ju4121.jpg",
 *          description="파일 url"
 *      )
 *  )
 */
class AttachThumb extends AttachFile
{
    use HasFactory;

    protected $table = 'attach_files';
    protected $hidden = ['attachable_type', 'attachable_id', 'user_id', 'created_at', 'updated_at', 'deleted_at'];
    protected $with = [];

    public function attachable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'attachable_type', 'attachable_id');
    }

    public function scopeSimplify($query)
    {
        return $query->select('id', 'url', 'attachable_type', 'attachable_id');
    }
}
