<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;


/**
 *
 *  @OA\Schema(
 *      @OA\Property(property="id", type="integer", example=1, description="고유 번호" ),
 *      @OA\Property(property="title", type="string", example="1:1 문의 제목", description="1:1문의 제목" ),
 *      @OA\Property(property="question", type="string", example="1:1 문의 내용", description="1:1문의 내용" ),
 *      @OA\Property(property="status", type="string", example="waiting", description="처리상태<br/>waiting:접수<br/>answering:확인중<br/>answered:완료" ),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="등록 일자", readOnly="true"),
 *      @OA\Property(property="updatedAt", type="string", format="date-time", description="수정 일자", readOnly="true")
 *  )
 */
class Inquiry extends Model
{
    use HasFactory, SoftDeletes;

    public $status = [
        'waiting',       // 접수
        'answering',    // 확인중
        'answered',     // 완료
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at'
    ];

    protected $appends = [
    ];

    protected $casts = [
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)
            ->select(['id', 'name', 'email']);
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id', 'id')
            ->select(['id', 'name', 'email']);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id', 'id')
            ->select(['id', 'name', 'email']);
    }

    public function answer(): HasOne
    {
        return $this->hasOne(InquiryAnswer::class)
            ->select(['id', 'user_id', 'inquiry_id', 'answer', 'created_at']);
    }

    public function attachFiles(): MorphMany
    {
        return $this->morphMany(AttachFile::class, 'attachable')
            ->select('id', 'url', 'attachable_id', 'attachable_type');
    }

    public function getCreatedAtAttribute($value): string
    {
        return Carbon::parse($value)->format('c');
    }

    public function getUpdatedAtAttribute($value): ?string
    {
        return $value ? Carbon::parse($value)->format('c') : $value;
    }

    // 파일 첨부 갯수 제한
    public function getAttachFileLimit(): int
    {
        return 10;
    }
}
