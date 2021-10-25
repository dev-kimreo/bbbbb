<?php

namespace App\Models\LinkedComponents;

use App\Models\Components\ComponentOption;
use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *      @OA\Property(property="id", type="integer", example=1, description="고유번호"),
 *      @OA\Property(property="component_option_id", type="integer", example=1, description="컴포넌트 옵션 고유 번호"),
 *      @OA\Property(property="linked_component_id", type="integer", example=1, description="연동 컴포넌트 고유 번호"),
 *      @OA\Property(property="value", type="text", example="연동 컴포넌트 옵션 값", description="연동 컴포넌트 옵션 값"),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="등록 일자"),
 *      @OA\Property(property="updatedAt", type="string", format="date-time", description="수정 일자")
 * )
 */
class LinkedComponentOption extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;


    protected $fillable = ['component_option_id', 'linked_component_id', 'value'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function componentOption(): BelongsTo
    {
        return $this->belongsTo(ComponentOption::class, 'component_option_id');
    }

}

