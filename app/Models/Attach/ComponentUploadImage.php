<?php

namespace App\Models\Attach;

use App\Models\Traits\DateFormatISO8601;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 *  @OA\Schema(
 *      schema="ComponentUploadImage",
 *      @OA\Property(property="id", type="integer", example=1, description="컴포넌트 업로드 이미지 고유 번호" ),
 *      @OA\Property(property="userId", type="integer", example=1, description="이미지를 업로드한 회원의 고유 번호" ),
 *      @OA\Property(property="width", type="integer", example="1024", description="이미지 너비(pixel)" ),
 *      @OA\Property(property="height", type="integer", example="768", description="이미지 높이(pixel)" )
 *  )
 *
 * @method static findOrFail(int $id)
 */
class ComponentUploadImage extends Model
{
    use HasFactory;
    use DateFormatISO8601;

    public $timestamps = false;
    protected $fillable = ['user_id', 'width', 'height'];
    protected $hidden = ['created_at', 'deleted_at'];
    protected $casts = [];
    protected $appends = [];
    protected $with = [];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = $model->created_at ?? $model->freshTimestamp();
        });

        static::deleting(function ($model) {
            $model->attachFile->delete();
        });
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attachFile(): MorphOne
    {
        return $this->morphOne(AttachFile::class, 'attachable');
    }
}
