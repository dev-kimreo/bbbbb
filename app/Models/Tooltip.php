<?php

namespace App\Models;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *  @OA\Schema(
 *     schema="Tooltip",
 *     @OA\Property(property="id", type="integer", example=23, description="툴팁 고유번호"),
 *     @OA\Property(property="userId", type="integer", example=1, description="작성한 관리자의 회원 고유번호"),
 *     @OA\Property(property="type", type="string", example="헬프센터", description="전시구분"),
 *     @OA\Property(property="title", type="string", example="1부터 100 사이의 숫자로 입력", description="툴팁 제목"),
 *     @OA\Property(property="visible", type="boolean", example="1", description="노출 또는 숨김 여부<br/>true:노출<br/>false:숨김"),
 *     @OA\Property(property="createdAt", type="datetime", example="2021-04-08T07:04:52+00:00", description="게시글 작성일자" ),
 *     @OA\Property(property="updatedAt", type="datetime", example="2021-04-08T07:57:55+00:00", description="게시글 수정일자" ),
 *     @OA\Property(property="contents", type="object", ref="#/components/schemas/TranslationContentsSimply"),
 *     @OA\Property(property="user", type="object", ref="#/components/schemas/UserSimply"),
 *     @OA\Property(property="backofficeLogs", type="array", @OA\Items(ref="#/components/schemas/BackofficeLog")),
 *  )
 *
 *  @OA\Schema(
 *     schema="TooltipForList",
 *     @OA\Property(property="id", type="integer", example=1, description="툴팁 고유번호"),
 *     @OA\Property(property="userId", type="integer", example=1, description="작성한 관리자의 회원 고유번호"),
 *     @OA\Property(property="type", type="string", example="헬프센터", description="전시구분"),
 *     @OA\Property(property="title", type="string", example="1부터 100 사이의 숫자로 입력", description="툴팁 제목"),
 *     @OA\Property(property="visible", type="boolean", example="1", description="노출 또는 숨김 여부<br/>true:노출<br/>false:숨김"),
 *     @OA\Property(property="createdAt", type="datetime", example="2021-04-08T07:04:52+00:00", description="게시글 작성일자" ),
 *     @OA\Property(property="updatedAt", type="datetime", example="2021-04-08T07:57:55+00:00", description="게시글 수정일자" ),
 *     @OA\Property(property="lang", type="array", @OA\Items(example="en"), description="콘텐츠가 등록된 언어 목록" ),
 *     @OA\Property(property="user", type="object", ref="#/components/schemas/UserSimply"),
 *  )
 *
 * Class Tooltip
 * @package App\Models
 */
class Tooltip extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    protected $appends = ['code'];
    protected $fillable = ['user_id', 'type', 'title', 'visible'];
    protected $hidden = ['deleted_at'];
    protected $casts = [
        'visible' => 'boolean'
    ];

    public static array $prefixes = [
        'SV' => '서비스소개',
        'HP' => '헬프센터',
        'AD' => '어드민',
        'BO' => '백오피스',
        'PL' => '플러그인',
        'ST' => '스토어',
        'PT' => '파트너센터'
    ];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($tooltip) {
            $tooltip->translation()->each(function($o){
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
        return $this->morphMany(BackofficeLog::class, 'loggable')
            ->orderByDesc('id');
    }

    public function getCodeAttribute(): string
    {
        $prefix = collect(self::$prefixes)->search($this->attributes['type']);
        return $prefix . '_' . str_pad($this->attributes['id'], 4, '0', STR_PAD_LEFT);
    }

    public function setCodeAttribute($v)
    {
        $this->attributes['type'] = self::$prefixes[strstr($v, '_', true)];
        $this->attributes['id'] = substr(strrchr($v, "_"), 1);
    }

    public function scopeWhereCodeIs($q, $v)
    {
        return $q
            ->where('type', self::$prefixes[strstr($v, '_', true)] ?? '')
            ->where('id', intval(substr(strrchr($v, "_"), 1)));
    }

    public function scopeWhereHasLanguage($q, $v)
    {
        return $q->whereHas('translation', function(Builder $q) use ($v) {
            $q->whereHas('translationContents', function (Builder $q) use ($v) {
                $q->where('lang', $v);
            });
        });
    }
}
