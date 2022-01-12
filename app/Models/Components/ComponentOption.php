<?php

namespace App\Models\Components;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 * @OA\Schema(
 *      @OA\Property(property="id", type="integer", example=1, description="고유번호"),
 *      @OA\Property(property="component_version_id", type="integer", example=1, description="컴포넌트 버전 고유번호"),
 *      @OA\Property(property="component_type_id", type="integer", example=1, description="컴포넌트 옵션 유형 고유번호"),
 *      @OA\Property(property="name", type="string", example="대 배너", description="컴포넌트 옵션명"),
 *      @OA\Property(property="key", type="string", example="bigBanner", description="컴포넌트 옵션 key"),
 *      @OA\Property(property="display_on_pc", type="boolean", example=false, description="컴포넌트 옵션 PC 노출여부"),
 *      @OA\Property(property="display_on_mobile", type="boolean", example=false, description="컴포넌트 옵션 Mobile 노출여부"),
 *      @OA\Property(property="hideable", type="boolean", example=false, description="컴포넌트 옵션 사용 여부를 제어하는 토글 노출여부"),
 *      @OA\Property(property="attributes", type="string", description="컴포넌트 옵션의 상세 설정 옵션 값"),
 *      @OA\Property(property="help", type="string", description="컴포넌트 옵션 도움말"),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="등록 일자"),
 *      @OA\Property(property="updatedAt", type="string", format="date-time", description="수정 일자")
 * )
 *
 */
class ComponentOption extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    public static string $exceptionEntity = "componentOption";

    protected $fillable = [
        'component_version_id', 'component_type_id', 'name', 'key', 'display_on_pc', 'display_on_mobile', 'hideable', 'help'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];


    public function type(): belongsTo
    {
        return $this->belongsTo(ComponentType::class, 'component_type_id', 'id');
    }

    public function properties(): hasMany
    {
        return $this->hasMany(ComponentOptionProperty::class, 'component_option_id', 'id');
    }

    public function version(): belongsTo
    {
        return $this->belongsTo(ComponentVersion::class, 'component_version_id', 'id');
    }
}

