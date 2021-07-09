<?php

namespace App\Models\Exhibitions;

use App\Models\ActionLog;
use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *  @OA\Schema(
 *     schema="ExhibitionCategory",
 *      @OA\Property(property="id", type="integer", example=1, description="고유 번호" ),
 *      @OA\Property(property="name", type="string", example="메인 중앙배너", description="제목" ),
 *      @OA\Property(property="url", type="url", example="https://help.qpick.com/board/1", description="링크 URL" ),
 *      @OA\Property(property="division", type="string", example="1:1 문의 내용", description="popup:팝업용 카테고리<br />banner:배너용 카테고리" ),
 *      @OA\Property(property="site", type="string", example="헬프센터", description="사이트명 (아래 항목 중 1개 출력)<br />서비스안내, 헬프센터, 어드민, 백오피스"),
 *      @OA\Property(property="max", type="integer", example="10", description="최대 표시개수" ),
 *      @OA\Property(property="enable", type="boolean", example=true, description="사용 가능 여부" ),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="등록일자"),
 *      @OA\Property(property="updatedAt", type="string", format="date-time", description="수정일자")
 *  )
 *
 * Class ExhibitionCategory
 * @package App\Models\Exhibitions
 * @method static findOrFail(int $category_id)
 * @method static orderByDesc(string $string)
 * @method static create(array $all)
 */
class ExhibitionCategory extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    protected $fillable = ['name', 'url', 'division', 'site', 'max', 'enable'];
    protected $hidden = ['deleted_at'];
    protected $casts = [
        'enable' => 'boolean'
    ];
    protected $appends = [];

    public static array $divisions = [
        'popup', 'banner'
    ];
    public static array $sites = [
        '서비스안내', '헬프센터', '어드민', '백오피스'
    ];

    public function exhibition(): HasMany
    {
        return $this->hasMany(Exhibition::class);
    }

    public function scopeSimplify($query)
    {
        return $query->select('id', 'name');
    }

    public function backofficeLogs(): MorphMany
    {
        return $this->morphMany(ActionLog::class, 'loggable');
    }

}
