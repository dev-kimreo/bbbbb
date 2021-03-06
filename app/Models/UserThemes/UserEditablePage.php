<?php

namespace App\Models\UserThemes;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 * @OA\Schema
 * @OA\Property(property="id", type="integer", example=1, description="고유 번호"),
 * @OA\Property(property="user_theme_id", type="integer", example=1, description="회원 테마 고유번호"),
 * @OA\Property(property="supported_editable_page_id", type="integer", example=1, description="지원 가능한 에디터 지원페이지 고유번호"),
 * @OA\Property(property="name", type="string", example="내 홈페이지 메인페이지", description="회원 에디터 지원페이지명"),
 * @OA\Property(property="createdAt", type="string", format="date-time", description="등록 일자"),
 * @OA\Property(property="updatedAt", type="string", format="date-time", description="수정 일자")
 * )
 *
 */
class UserEditablePage extends Model
{
    use HasFactory;
    use SoftDeletes;
    use DateFormatISO8601;

    public static string $exceptionEntity = "userEditablePage";
    protected $fillable = ['user_theme_id', 'supported_editable_page_id', 'name'];
    protected $hidden = [];

    public function userTheme(): BelongsTo
    {
        return $this->belongsTo(UserTheme::class);
    }

}
