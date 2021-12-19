<?php

namespace App\Models\Components;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 * @OA\Schema(
 *      @OA\Property(property="id", type="integer", example=1, description="고유번호"),
 *      @OA\Property(property="name", type="string", example="Toggle", description="컴포넌트 유형 명"),
 *      @OA\Property(property="is_plural", type="boolean", example=false, description="데이터의 복수 여부"),
 *      @OA\Property(property="has_option", type="boolean", example=false, description="선택지 설정 여부"),
 *      @OA\Property(property="has_default", type="boolean", example=false, description="기본 값 설정 여부"),
 *      @OA\Property(property="max_count", type="integer", example=1, description="최대 항목 수"),
 *      @OA\Property(property="attributes", type="string", description="컴포넌트 유형의 상세 설정 옵션 값"),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="등록 일자"),
 *      @OA\Property(property="updatedAt", type="string", format="date-time", description="수정 일자")
 * )
 *
 */
class ComponentType extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601;


    protected $fillable = [
        'name', 'is_plural', 'has_option', 'has_default', 'max_count', 'attributes'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];


    protected $casts = [
        'attributes' => 'array'
    ];

    public function properties(): HasMany
    {
        return $this->hasMany(ComponentTypeProperty::class, 'component_type_id', 'id');
    }




}

