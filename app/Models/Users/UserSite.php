<?php

namespace App\Models\Users;

use App\Models\Solution;
use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use JetBrains\PhpStorm\ArrayShape;

/**
 *
 * @OA\Schema(
 *   schema="UserSite",
 *   @OA\Property(property="id", type="integer", example="12"),
 *   @OA\Property(property="userId", type="integer", description="사용자의 고유번호(PK)", example="27"),
 *   @OA\Property(property="userSolutionId", type="integer", description="솔루션 고유번호(PK)", example="3"),
 *   @OA\Property(property="name", type="string", maxLength=32, description="사이트명", example="J맨즈 컬렉션"),
 *   @OA\Property(property="url", type="string", maxLength=256, description="사이트 URL", example="https://jmans.co.kr"),
 *   @OA\Property(property="bizType", type="string", maxLength=16, description="쇼핑몰 분류", example="남성의류"),
 *   @OA\Property(property="createdAt", ref="#/components/schemas/Base/properties/created_at"),
 *   @OA\Property(property="updatedAt", ref="#/components/schemas/Base/properties/updated_at"),
 *   @OA\Property(property="userSolution", ref="#/components/schemas/UserSolutionWithRelation")
 * )
 *
 */
class UserSite extends Model
{
    use HasFactory;
    use SoftDeletes;
    use DateFormatISO8601;
    use CheckUpdatedAt;

    public static string $exceptionEntity = "userSite";

    protected $fillable = ['user_id', 'user_solution_id', 'name', 'url', 'biz_type'];
    protected $hidden = ['deleted_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userSolution(): BelongsTo
    {
        return $this->belongsTo(UserSolution::class);
    }
}
