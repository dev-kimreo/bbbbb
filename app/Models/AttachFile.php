<?php

namespace App\Models;

use App\Models\Traits\DateFormatISO8601;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *  @OA\Schema(
 *      @OA\Xml(name="이미지"),
 *      @OA\Property(property="id", type="integer", example=1, description="업로드 파일 고유 번호" ),
 *      @OA\Property(property="server", type="string", example="public", description="업로드된 서버" ),
 *      @OA\Property(property="attachableType", type="string", example="temp", description=" 업로드 타입" ),
 *      @OA\Property(property="attachableId", type="integer", example=1, description="타입의 고유 번호" ),
 *      @OA\Property(property="userId", type="integer", example=1, description="회원 고유 번호" ),
 *      @OA\Property(property="url", type="string", example="https://qpicki.com/storage/temp/123asfd12ju4121.jpg", description="파일 url" ),
 *      @OA\Property(property="path", type="string", example="temp", description="파일 경로" ),
 *      @OA\Property(property="name", type="string", example="123asfd12ju4121.jpg", description="파일 이름" ),
 *      @OA\Property(property="orgName", type="string", example="홍길동.jpg", description="파일 원래 이름" ),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="등록 일자", readOnly="true"),
 *      @OA\Property(property="updatedAt", type="string", format="date-time", description="수정 일자", readOnly="true")
 *  )
 *
 *  @OA\Schema(
 *      schema="AttachFileSimply",
 *      @OA\Property(property="id", type="integer", example=1, description="업로드 파일 고유 번호" ),
 *      @OA\Property(property="url", type="string", example="https://qpicki.com/storage/temp/123asfd12ju4121.jpg", description="파일 url" ),
 *      @OA\Property(property="attachableType", type="string", example="temp", description=" 업로드 타입" ),
 *      @OA\Property(property="attachableId", type="integer", example=1, description="타입의 고유 번호" )
 *  )
 *
 *
 * @method static create(array $insertArr)
 * @method static where(array $array)
 */
class AttachFile extends Model
{
    use HasFactory;
    use SoftDeletes;
    use DateFormatISO8601;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'server', 'attachable_type', 'attachable_id', 'user_id', 'url', 'path', 'name', 'org_name', 'etc', 'size'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    protected $casts = [
        'etc' => 'array'
    ];

    protected array $maps = [
    ];

    protected $appends = [
    ];

    public function scopeTempType($q)
    {
        return $q->where('attachable_type', 'temp');
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'attachable_type', 'attachable_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
