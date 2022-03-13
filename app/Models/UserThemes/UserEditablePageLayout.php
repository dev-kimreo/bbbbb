<?php

namespace App\Models\UserThemes;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 * @OA\Schema
 * @OA\Property(property="id", type="integer", example=1, description="고유 번호"),
 * @OA\Property(property="user_editable_page_id", type="integer", example=1, description="회원 에디터 지원페이지 고유번호"),
 * @OA\Property(property="header_component_group_id", type="integer", example=1, description="Header의 연동 컴포넌트 그룹 고유 번호"),
 * @OA\Property(property="content_component_group_id", type="integer", example=2, description="Content의 연동 컴포넌트 그룹 고유 번호"),
 * @OA\Property(property="footer_component_group_id", type="integer", example=3, description="Footer의 연동 컴포넌트 그룹 고유 번호"),
 * @OA\Property(property="createdAt", type="string", format="date-time", description="등록 일자"),
 * @OA\Property(property="updatedAt", type="string", format="date-time", description="수정 일자")
 * )
 *
 */
class UserEditablePageLayout extends Model
{
    use HasFactory;
    use SoftDeletes;
    use DateFormatISO8601;

    public static string $exceptionEntity = "userEditablePageLayout";
    protected $fillable = ['user_editable_page_id', 'header_component_group_id', 'content_component_group_id', 'footer_component_group_id'];
    protected $hidden = [];

    public function userTheme(): BelongsTo
    {
        return $this->belongsTo(UserTheme::class);
    }

    public function editablePage(): HasOne
    {
        return $this->hasOne(UserEditablePage::class, 'id', 'user_editable_page_id');
    }

//    public function linkedHeaderComponentGroup(): BelongsTo
//    {
//        return $this->belongsTo(LinkedComponentGroup::class, 'header_component_group_id');
//    }
//
//    public function linkedContentComponentGroup(): BelongsTo
//    {
//        return $this->belongsTo(LinkedComponentGroup::class, 'content_component_group_id');
//    }
//
//    public function linkedFooterComponentGroup(): BelongsTo
//    {
//        return $this->belongsTo(LinkedComponentGroup::class, 'footer_component_group_id');
//    }
}
