<?php

namespace App\Models\Components;

use App\Models\SupportedEditablePage;
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
 *      @OA\Property(property="component_id", type="integer", example=1, description="컴포넌트 고유 번호"),
 *      @OA\Property(property="supported_editable_page_id", type="integer", example=1, description="지원가능한 에디터 지원페이지 고유 번호"),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="등록 일자"),
 *      @OA\Property(property="updatedAt", type="string", format="date-time", description="수정 일자")
 * )
 *
 */
class ComponentUsablePage extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    public static string $exceptionEntity = "componentUsablePage";

    protected $fillable = [
        'component_id', 'supported_editable_page_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class);
    }

    public function supportedEditablePage(): BelongsTo
    {
        return $this->belongsTo(SupportedEditablePage::class);
    }

}

