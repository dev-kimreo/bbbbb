<?php

namespace App\Models\EditablePages;

use App\Models\SupportedEditablePage;
use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 * @OA\Schema(
 *      @OA\Property(property="id", type="integer", example=1, description="고유번호"),
 *      @OA\Property(property="theme_id", type="integer", example=1, description="테마 고유 번호"),
 *      @OA\Property(property="supported_editable_page_id", type="integer", example=1, description="지원 가능한 에디터 지원 페이지 고유 번호"),
 *      @OA\Property(property="name", type="string", example="에디터 지원 페이지 ", description="에디터 지원 페이지명"),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="등록 일자"),
 *      @OA\Property(property="updatedAt", type="string", format="date-time", description="수정 일자")
 * )
 *
 * @method static where(mixed $param)
 * @method static create(array|int[] $array_merge)
 * @method static findOrFail($editablePageId)
 */
class EditablePage extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;


    protected $fillable = [
        'theme_id', 'supported_editable_page_id', 'name'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];


    public function supportedEditablePage(): BelongsTo
    {
        return $this->belongsTo(SupportedEditablePage::class, 'supported_editable_page_id');
    }

    public function editablePageLayout(): HasOne
    {
        return $this->hasOne(EditablePageLayout::class);
    }

}

