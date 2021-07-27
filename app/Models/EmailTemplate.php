<?php

namespace App\Models;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *  @OA\Schema(
 *     schema="EmailTemplate",
 *     @OA\Property(property="id", type="integer", example=23, description="메일 템플릿 고유번호"),
 *     @OA\Property(property="userId", type="integer", example=1, description="작성한 관리자의 회원 고유번호"),
 *     @OA\Property(property="code", type="string", example="USER_REGISTED", description="메일 템플릿 코드"),
 *     @OA\Property(property="name", type="string", example="[회원] 회원가입 완료 메일", description="메일 템플릿 명"),
 *     @OA\Property(property="title", type="string", example="{{$name}}님의 가입을 축하합니다.", description="메일 제목"),
 *     @OA\Property(property="createdAt", type="datetime", example="2021-04-08T07:04:52+00:00", description="작성일자" ),
 *     @OA\Property(property="updatedAt", type="datetime", example="2021-04-08T07:57:55+00:00", description="수정일자" ),
 *     @OA\Property(property="user", type="object", ref="#/components/schemas/UserSimply"),
 *     @OA\Property(property="backofficeLogs", type="array", @OA\Items(ref="#/components/schemas/BackofficeLog")),
 *  )
 *
 *  @OA\Schema(
 *     schema="EmailTemplateForList",
 *     @OA\Property(property="id", type="integer", example=23, description="메일 템플릿 고유번호"),
 *     @OA\Property(property="userId", type="integer", example=1, description="작성한 관리자의 회원 고유번호"),
 *     @OA\Property(property="code", type="string", example="USER_REGISTED", description="메일 템플릿 코드"),
 *     @OA\Property(property="name", type="string", example="[회원] 회원가입 완료 메일", description="메일 템플릿 명"),
 *     @OA\Property(property="title", type="string", example="{{$name}}님의 가입을 축하합니다.", description="메일 제목"),
 *     @OA\Property(property="createdAt", type="datetime", example="2021-04-08T07:04:52+00:00", description="작성일자" ),
 *     @OA\Property(property="updatedAt", type="datetime", example="2021-04-08T07:57:55+00:00", description="수정일자" ),
 *     @OA\Property(property="user", type="object", ref="#/components/schemas/UserSimply"),
 *  )
 *
 * @method static create(array|int[]|null[]|string[] $array_merge)
 * @method static findOrFail(int $id)
 */
class EmailTemplate extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    protected $appends = [];
    protected $fillable = ['code', 'user_id', 'name', 'title'];
    protected $hidden = ['enable', 'ignore_agree', 'deleted_at'];
    protected $casts = [
        'enable' => 'boolean'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->simplify('manager');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function backofficeLogs(): MorphMany
    {
        return $this->morphMany(ActionLog::class, 'loggable')->forBackoffice();
    }
}
