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
 * )
 *
 */
class ComponentVersion extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;


    protected $fillable = [
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    // 컴포넌트 아이콘
    public static array $icon = [
        'header' => '헤더',
        'footer' => '푸터',
        'category' => '카테고리',
        'image' => '이미지',
        'product' => '상품',
        'text' => '텍스트',
        'plugin' => '플러그인',
        'solution' => '솔루션'
    ];

    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }

    public function option(): HasMany
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

