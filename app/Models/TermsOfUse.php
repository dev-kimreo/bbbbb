<?php

namespace App\Models;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="TermsOfUse",
 *     @OA\Property(property="id", type="integer", example=23, description="이용약관&개인정보처리방침 고유번호"),
 *     @OA\Property(property="userId", type="integer", example=1, description="작성한 관리자의 회원 고유번호"),
 *     @OA\Property(property="type", type="string", example="이용약관", description="구분 (이용약관, 개인정보처리방침)"),
 *     @OA\Property(property="title", type="string", example="이용약관 제목", description="이용약관&개인정보처리방침 제목"),
 *     @OA\Property(property="startedAt", type="datetime", example="2021-06-05T09:00:00+00:00", description="전시 시작일"),
 *     @OA\Property(property="history", type="string", example="변경내역", description="변경내역"),
 *     @OA\Property(property="createdAt", type="datetime", example="2021-04-08T07:04:52+00:00", description="작성일자" ),
 *     @OA\Property(property="updatedAt", type="datetime", example="2021-04-08T07:57:55+00:00", description="수정일자" ),
 *     @OA\Property(property="contents", type="object", ref="#/components/schemas/TranslationContentsSimply"),
 *     @OA\Property(property="user", type="object", ref="#/components/schemas/UserSimply"),
 *     @OA\Property(property="backofficeLogs", type="array", @OA\Items(ref="#/components/schemas/BackofficeLog")),
 *  )
 *
 *
 * @OA\Schema(
 *     schema="TermsOfUseForList",
 *     @OA\Property(property="id", type="integer", example=1, description="이용약관&개인정보처리방침 고유번호"),
 *     @OA\Property(property="userId", type="integer", example=1, description="작성한 관리자의 회원 고유번호"),
 *     @OA\Property(property="type", type="string", example="이용약관", description="구분 (이용약관, 개인정보처리방침)"),
 *     @OA\Property(property="title", type="string", example="이용약관 제목", description="이용약관&개인정보처리방침 제목"),
 *     @OA\Property(property="startedAt", type="datetime", example="2021-06-05T09:00:00+00:00", description="전시 시작일"),
 *     @OA\Property(property="history", type="string", example="변경내역", description="변경내역"),
 *     @OA\Property(property="createdAt", type="datetime", example="2021-04-08T07:04:52+00:00", description="작성일자" ),
 *     @OA\Property(property="updatedAt", type="datetime", example="2021-04-08T07:57:55+00:00", description="수정일자" ),
 *     @OA\Property(property="lang", type="array", @OA\Items(example="en"), description="콘텐츠가 등록된 언어 목록" ),
 *  )
 *
 * @OA\Schema(
 *     schema="TermsOfUseTypeList",
 *     @OA\Property(property="termsOfUse", type="string", example="이용약관"),
 *     @OA\Property(property="privacyPolicy", type="string", example="개인정보처리방침"),
 * )
 *
 * @OA\Schema(
 *     schema="TermsOfUseServiceList",
 *     @OA\Property(property="qpick", type="string", example="큐픽 서비스"),
 *     @OA\Property(property="partner", type="string", example="파트너센터"),
 * )
 *
 * Class TermsOfUse
 * @package App\Models
 * @method whereHasLanguage($v)
 * @method static findOrFail(int $id)
 * @method static create(array|\Carbon\Carbon[]|int[]|null[]|string[] $array_merge)
 */
class TermsOfUse extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    public static string $exceptionEntity = "termsOfUse";

    protected $fillable = ['user_id', 'type', 'service', 'title', 'started_at', 'history'];
    protected $hidden = ['deleted_at'];
    protected $casts = [
        'started_at' => 'datetime'
    ];

    public static array $services = [
        'qpick' => '큐픽 서비스',
        'partner' => '파트너센터'
    ];


    public static array $types = [
        'termsOfUse' => '이용약관',
        'privacyPolicy' => '개인정보처리방침'
    ];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($tooltip) {
            $tooltip->translation()->each(function ($o) {
                $o->delete();
            });
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->simplify('manager');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function translation(): MorphOne
    {
        return $this->morphOne(Translation::class, 'linkable');
    }

    public function backofficeLogs(): MorphMany
    {
        return $this->morphMany(ActionLog::class, 'loggable')->forBackoffice();
    }

    public function scopeWhereHasLanguage($q, $v)
    {
        return $q->whereHas('translation', function (Builder $q) use ($v) {
            $q->whereHas('translationContents', function (Builder $q) use ($v) {
                $q->where('lang', $v);
            });
        });
    }

    public function getServiceKorAttribute()
    {
        return self::$services[$this->service];
    }
}
