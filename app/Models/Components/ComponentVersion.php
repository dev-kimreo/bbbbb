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
 *      @OA\Property(property="component_id", type="integer", example=1, description="컴포넌트 고유 번호"),
 *      @OA\Property(property="usable", type="boolean", example=false, default=false, description="컴포넌트 버전 사용 여부"),
 *      @OA\Property(property="template", type="string", description="컴포넌트 버전의 template 코드"),
 *      @OA\Property(property="script", type="string", description="컴포넌트 버전의 script 코드"),
 *      @OA\Property(property="style", type="string", description="컴포넌트 버전의 style 코드"),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="등록 일자"),
 *      @OA\Property(property="updatedAt", type="string", format="date-time", description="수정 일자")
 * )
 *
 */
class ComponentVersion extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601;

    public static string $exceptionEntity = "componentVersion";

    protected $fillable = [
        'template', 'style', 'script', 'component_id', 'usable'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    // 컴포넌트 버전 갯수 제한
    public static int $limitCount = 3;

    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(ComponentOption::class, 'component_version_id', 'id');
    }

    public function getRenderDataAttribute(): array
    {
        return [
            'template' => $this->template,
            'style' => $this->style,
            'script' => $this->script
        ];
    }
}

