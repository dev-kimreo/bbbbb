<?php

namespace App\Models\LinkedComponents;

use App\Models\Components\Component;
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
 *      @OA\Property(property="linked_component_group_id", type="integer", example=1, description="연동 컴포넌트 그룹 고유 번호"),
 *      @OA\Property(property="component_id", type="integer", example=1, description="컴포넌트 고유 번호"),
 *      @OA\Property(property="name", type="string", example="메인배너 컴포넌트", description="연동 컴포넌트 명"),
 *      @OA\Property(property="sort", type="integer", example=1, description="연동 컴포넌트 정렬 순서"),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="등록 일자"),
 *      @OA\Property(property="updatedAt", type="string", format="date-time", description="수정 일자")
 * )
 *
 */
class LinkedComponent extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;


    protected $fillable = [
        'linked_component_group_id', 'component_id', 'name', 'sort'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];


    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class, 'component_id');
    }

    public function linkedOption(): HasMany
    {
        return $this->hasMany(LinkedComponentOption::class, 'linked_component_id', 'id');
    }
}

