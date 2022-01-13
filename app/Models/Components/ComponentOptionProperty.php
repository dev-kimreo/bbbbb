<?php

namespace App\Models\Components;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 * @OA\Schema(
 *      @OA\Property(property="id", type="integer", example=1, description="고유번호"),
 *      @OA\Property(property="componentOptionId", type="integer", example=1, description="컴포넌트 옵션 고유번호"),
 *      @OA\Property(property="componentTypePropertyId", type="integer", example=1, description="컴포넌트 옵션유형 속성의 고유번호"),
 *      @OA\Property(property="key", type="string", example="color", description="연동 컴포넌트 옵션에서 변수명처럼 사용할 속성의 이름"),
 *      @OA\Property(property="name", type="string", example="배경색 색상", description="속성명"),
 *      @OA\Property(property="initialValue", type="string", example="#FFCCDD00", description="초기값"),
 *      @OA\Property(property="elements", type="JSON", example="color", description="파트너사가 입력한 각종 설정"),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="등록 일자"),
 *      @OA\Property(property="updatedAt", type="string", format="date-time", description="수정 일자")
 * )
 *
 */
class ComponentOptionProperty extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601;

    public static string $exceptionEntity = "componentOptionProperty";

    protected $fillable = [
        'component_option_id', 'component_type_property_id', 'key', 'name', 'initial_value', 'elements'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];
    protected $casts = ['elements' => 'array'];

    public function property(): belongsTo
    {
        return $this->belongsTo(ComponentTypeProperty::class, 'component_type_property_id', 'id');
    }

    public function option(): belongsTo
    {
        return $this->belongsTo(ComponentOption::class, 'component_option_id', 'id');
    }
}

