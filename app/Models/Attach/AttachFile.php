<?php

namespace App\Models\Attach;

use App\Models\Traits\DateFormatISO8601;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *  @OA\Schema(
 *      schema="AttachFile",
 *      @OA\Xml(name="이미지"),
 *      @OA\Property(property="id", type="integer", example=1, description="업로드 파일 고유 번호" ),
 *      @OA\Property(property="server", type="string", example="public", description="업로드된 서버" ),
 *      @OA\Property(property="attachableType", type="string", example="temp", description=" 업로드 타입" ),
 *      @OA\Property(property="attachableId", type="integer", example=1, description="타입의 고유 번호" ),
 *      @OA\Property(property="userId", type="integer", example=1, description="파일을 업로드한 회원의 고유 번호" ),
 *      @OA\Property(
 *          property="url",
 *          type="string",
 *          example="https://qpicki.com/storage/temp/123asfd12ju4121.jpg",
 *          description="파일 url"
 *      ),
 *      @OA\Property(property="path", type="string", example="temp", description="파일 경로" ),
 *      @OA\Property(property="name", type="string", example="123asfd12ju4121.jpg", description="파일 이름" ),
 *      @OA\Property(property="orgName", type="string", example="홍길동.jpg", description="파일 원래 이름" ),
 *      @OA\Property(property="size", type="integer", example="8402", description="파일의 크기(byte)" ),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="등록 일자", readOnly="true"),
 *      @OA\Property(property="updatedAt", type="string", format="date-time", description="수정 일자", readOnly="true"),
 *      @OA\Property(property="thumb", type="object", ref="#/components/schemas/AttachThumb"),
 *  )
 *
 *  @OA\Schema(
 *      schema="AttachFileSimply",
 *      @OA\Property(property="id", type="integer", example=1, description="업로드 파일 고유 번호" ),
 *      @OA\Property(
 *          property="url",
 *          type="string",
 *          example="https://qpicki.com/storage/temp/123asfd12ju4121.jpg",
 *          description="파일 url"
 *      ),
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

    protected $fillable = [
        'server', 'attachable_type', 'attachable_id', 'user_id', 'url', 'path', 'name', 'org_name', 'etc', 'size'
    ];
    protected $hidden = ['deleted_at'];
    protected $casts = ['etc' => 'array'];
    protected array $maps = [];
    protected $appends = [];
    protected $with = ['thumb'];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($attach) {
            $attach->thumb()->each(function ($o) {
                $o->delete();
            });
        });
    }

    public function scopeTempType($q)
    {
        return $q->where('attachable_type', 'temp');
    }

    public function attachable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'attachable_type', 'attachable_id');
    }

    public function thumb(): MorphOne
    {
        return $this->morphOne(AttachThumb::class, 'attachable');
    }

    public function thumbSimplify(): MorphOne
    {
        return $this->morphOne(AttachThumb::class, 'attachable')->simplify();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
