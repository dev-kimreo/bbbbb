<?php

namespace App\Models;

use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 *
 * @OA\Schema(
 * @OA\Property(property="id", type="integer", readOnly="true", example="1"),
 * @OA\Property(property="userId", type="integer", readOnly="true", description="사용자의 고유번호(PK)", example="1"),
 * @OA\Property(property="inquiryId", type="integer", readOnly="true", description="1:1상담 문의 고유번호(PK)", example="1"),
 * @OA\Property(property="answer", type="string", readOnly="true", description="답변내용", example="더 좋은 큐픽 서비스가 될 수 있도록 최선을 다하겠습니다."),
 * @OA\Property(
 *     property="createdAt", type="string", readOnly="true", format="date-time",
 *     ref="#/components/schemas/Base/properties/created_at", example="2021-02-25 12:59:20"
 * ),
 * @OA\Property(
 *     property="updatedAt", type="string", readOnly="true", format="date-time",
 *     ref="#/components/schemas/Base/properties/updated_at", example="2021-02-25 12:59:20"
 * )
 * )
 *
 * Class InquiryAnswer
 *
 */

class InquiryAnswer extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601;

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
        return $this->belongsTo(User::class)->simplify();
    }

    public function inquiry(): belongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function scopeSimplify($query)
    {
        return $query->select(['id', 'user_id', 'inquiry_id', 'answer', 'created_at']);
    }
}
