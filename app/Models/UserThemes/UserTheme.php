<?php

namespace App\Models\UserThemes;

use App\Models\Themes\Theme;
use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 * @OA\Schema
 * @OA\Property(property="id", type="integer", example=1, description="고유 번호"),
 * @OA\Property(property="user_id", type="integer", example=1, description="회원 고유번호"),
 * @OA\Property(property="theme_id", type="integer", example=1, description="테마 고유번호"),
 * @OA\Property(property="name", type="string", example="내 홈페이지 테마", description="회원 테마 명"),
 * @OA\Property(property="createdAt", type="string", format="date-time", description="등록 일자"),
 * @OA\Property(property="updatedAt", type="string", format="date-time", description="수정 일자")
 * )
 *
 */
class UserTheme extends Model
{
    use HasFactory;
    use SoftDeletes;
    use DateFormatISO8601;
    use CheckUpdatedAt;

    public static string $exceptionEntity = "userTheme";
    protected $fillable = ['user_id', 'theme_id', 'name'];
    protected $hidden = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

}
