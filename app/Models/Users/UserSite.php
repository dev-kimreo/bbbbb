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
 *   @OA\Property(property="user_id", type="integer", description="사용자의 고유번호(PK)", example="27"),
 *   @OA\Property(property="user_solution_id", type="integer", description="솔루션 고유번호(PK)", example="3"),
 *   @OA\Property(property="name", type="string", maxLength=32, description="사이트명", example="J맨즈 컬렉션"),
 *   @OA\Property(property="url", type="string", maxLength=256, description="사이트 URL", example="https://jmans.co.kr"),
 *   @OA\Property(property="bizType", type="string", maxLength=16, description="쇼핑몰 분류", example="남성의류"),
 *   @OA\Property(property="solutionInfo",
 *      @OA\Property(property="id", type="integer", description="솔루션 연동정보 고유번호(PK)", example="3"),
 *      @OA\Property(property="name", type="string", maxLength=16, description="연동된 솔루션명", example="마이소호"),
 *      @OA\Property(property="apikey", type="string", maxLength=512, description="연동된 솔루션의 API Key", example="apikey31f7sdg6bsd73"),
 *      @OA\Property(property="solutionUserId", type="string", maxLength=128, description="연동된 솔루션 회원 ID", example="honggildong"),
 *   ),
 *   @OA\Property(property="createdAt", ref="#/components/schemas/Base/properties/created_at"),
 *   @OA\Property(property="updatedAt", ref="#/components/schemas/Base/properties/updated_at")
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
    protected $appends = ['solutionInfo'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userSolution(): BelongsTo
    {
        return $this->belongsTo(UserSolution::class);
    }

    #[ArrayShape(['id' => "int", 'name' => "string", 'apikey' => "string", 'solution_user_id' => "string"])]
    public function getSolutionInfoAttribute(): array
    {
        $userSolution = $this->userSolution()->first();
        return [
            'id' => intval($userSolution->id),
            'name' => strval($userSolution->solution->name),
            'apikey' => strval($userSolution->apikey),
            'solution_user_id' => strval($userSolution->solution_user_id)
        ];
    }
}
