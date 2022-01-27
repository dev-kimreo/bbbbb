<?php

namespace App\Models\LinkedComponents;

use App\Models\Components\Component;
use App\Models\ScriptRequest;
use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use App\Services\ComponentRenderingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 * @OA\Schema(
 *      @OA\Property(property="id", type="integer", example=1, description="고유번호"),
 *      @OA\Property(property="linked_component_group_id", type="integer", example=1, description="연동 컴포넌트 그룹 고유 번호"),
 *      @OA\Property(property="component_id", type="integer", example=1, description="컴포넌트 고유 번호"),
 *      @OA\Property(property="name", type="string", example="메인배너 컴포넌트", description="연동 컴포넌트 명"),
 *      @OA\Property(property="etc", type="string", example="", description="기타 정보(여백,등)"),
 *      @OA\Property(property="display_on_pc", type="boolean", example=true, description="PC 노출 여부"),
 *      @OA\Property(property="display_on_mobile", type="boolean", example=false, description="Mobile 노출 여부"),
 *      @OA\Property(property="sort", type="integer", example=1, description="연동 컴포넌트 정렬 순서"),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="등록 일자"),
 *      @OA\Property(property="updatedAt", type="string", format="date-time", description="수정 일자")
 * )
 *
 */
class LinkedComponent extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;


    public static string $exceptionEntity = "linkedComponent";

    protected $fillable = [
        'linked_component_group_id', 'component_id', 'name', 'sort', 'etc', 'display_on_pc', 'display_on_mobile'
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

    public function linkedOptions(): HasMany
    {
        return $this->hasMany(LinkedComponentOption::class, 'linked_component_id', 'id');
    }

    public function linkedComponentGroup(): BelongsTo
    {
        return $this->belongsTo(LinkedComponentGroup::class);
    }

    public function scriptRequest(): MorphOne
    {
        return $this->morphOne(ScriptRequest::class, 'requestable');
    }

    public function getRenderDataAttribute(): array
    {
        $rawSource = $this->component()->first()->usableVersion()->first();

        return [
            'template' => ComponentRenderingService::procTemplate($rawSource->template),
            'style' => ComponentRenderingService::procStyle($rawSource->style),
            'script' => ComponentRenderingService::generateUrl($this)
        ];
    }
}

