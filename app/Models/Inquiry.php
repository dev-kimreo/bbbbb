<?php

namespace App\Models;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 *  @OA\Schema(
 *     schema="Inquiry",
 *      @OA\Property(property="id", type="integer", example=1, description="고유 번호" ),
 *      @OA\Property(property="title", type="string", example="1:1 문의 제목", description="1:1문의 제목" ),
 *      @OA\Property(property="question", type="string", example="1:1 문의 내용", description="1:1문의 내용" ),
 *      @OA\Property(property="status", type="string", example="waiting", description="처리상태<br/>waiting:접수<br/>answering:확인중<br/>answered:완료" ),
 *      @OA\Property(property="assignedAt", type="string", format="date-time", description="처리담당자 지정일자"),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="1:1문의 등록일자"),
 *      @OA\Property(property="updatedAt", type="string", format="date-time", description="1:1문의 수정일자"),
 *      @OA\Property(property="user", type="object", ref="#/components/schemas/UserSimply", description="작성한 사용자 정보"),
 *      @OA\Property(property="referrer", type="object", ref="#/components/schemas/UserSimply", description="문의계정으로 지정된 사용자 정보"),
 *      @OA\Property(property="assignee", type="object", ref="#/components/schemas/UserSimply", description="처리담당자 정보"),
 *      @OA\Property(property="attachFiles", type="array", description="첨부파일", @OA\Items(ref="#/components/schemas/AttachFileSimply")),
 *      @OA\Property(property="backofficeLogs", type="array", description="백오피스 업데이트 로그", @OA\Items(ref="#/components/schemas/BackofficeLog")),
 *  )
 *
 *  @OA\Schema(
 *      schema="InquiryForList",
 *      @OA\Property(property="id", type="integer", example=1, description="고유 번호" ),
 *      @OA\Property(property="title", type="string", example="1:1 문의 제목", description="1:1문의 제목" ),
 *      @OA\Property(property="question", type="string", example="1:1 문의 내용", description="1:1문의 내용" ),
 *      @OA\Property(property="status", type="string", example="waiting", description="처리상태<br/>waiting:접수<br/>answering:확인중<br/>answered:완료" ),
 *      @OA\Property(property="assignedAt", type="string", format="date-time", description="처리담당자 지정일자"),
 *      @OA\Property(property="createdAt", type="ISO 8601 date", example="2021-02-12T15:19:21+00:00", description="등록일자"),
 *      @OA\Property(property="updatedAt", type="ISO 8601 date", example="2021-02-13T18:52:16+00:00", description="수정일자"),
 *      @OA\Property(property="answered", type="boolean", example="true", description="답변완료 여부"),
 *      @OA\Property(property="answeredAt", type="boolean", example="2021-02-13T18:52:16+00:00", description="답변완료일 (답변이 없는 경우 null)"),
 *      @OA\Property(property="attached", type="boolean", example="false", description="첨부파일 존재여부"),
 *      @OA\Property(property="user", type="object", ref="#/components/schemas/UserSimply", description="작성한 사용자 정보"),
 *      @OA\Property(property="referrer", type="object", ref="#/components/schemas/UserSimply", description="문의계정으로 지정된 사용자 정보"),
 *      @OA\Property(property="assignee", type="object", ref="#/components/schemas/UserSimply", description="처리담당자의 사용자 정보"),
 *  )
 * @method static findOrFail(int $inquiryId)
 * @method static find($id)
 * @method static selectRaw(string $string)
 */
class Inquiry extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    public static array $status = [
        'waiting' => 'waiting',       // 접수
        'answering' => 'answering',   // 확인중
        'answered' => 'answered',     // 완료
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
        'user_id', 'referrer_id', 'assignee_id', 'deleted_at'
    ];

    protected $appends = [
    ];

    protected $casts = [
        'assigned_at' => 'datetime'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->simplify('user');
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id', 'id')->simplify('user');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id', 'id')->simplify('manager');
    }

    public function answer(): HasOne
    {
        return $this->hasOne(InquiryAnswer::class)->with('user')->simplify('manager');
    }

    public function attachFiles(): MorphMany
    {
        return $this->morphMany(AttachFile::class, 'attachable')
            ->select('id', 'url', 'attachable_id', 'attachable_type');
    }

    public function backofficeLogs(): MorphMany
    {
        return $this->morphMany(ActionLog::class, 'loggable')->forBackoffice();
    }

    // 파일 첨부 갯수 제한
    public function getAttachFileLimit(): int
    {
        return 10;
    }
}
