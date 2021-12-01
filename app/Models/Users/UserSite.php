<?php

namespace App\Models\Users;

use App\Models\ActionLog;
use App\Models\Solution;
use App\Models\Traits\DateFormatISO8601;
use App\Models\Traits\CheckUpdatedAt;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 * @OA\Schema(
 *   schema="UserSite",
 *   @OA\Property(property="id", type="integer", example="12"),
 *   @OA\Property(property="user_id", type="integer", description="사용자의 고유번호(PK)", example="27"),
 *   @OA\Property(property="solution_id", type="integer", description="솔루션 고유번호(PK)", example="3"),
 *   @OA\Property(property="type", type="string", maxLength=16, description="쇼핑몰 분류", example="남성의류"),
 *   @OA\Property(property="name", type="string", maxLength=32, description="사이트명", example="J맨즈 컬렉션"),
 *   @OA\Property(property="url", type="string", maxLength=256, description="사이트 URL", example="https://jmans.co.kr"),
 *   @OA\Property(property="solution", type="string", maxLength=16, description="연동된 솔루션명", example="마이소호"),
 *   @OA\Property(property="solutionUserId", type="string", maxLength=128, description="연동된 솔루션 회원 ID", example="honggildong"),
 *   @OA\Property(property="apikey", type="string", maxLength=512, description="연동된 솔루션의 API Key", example="apikey31f7sdg6bsd73"),
 *   @OA\Property(property="createdAt", ref="#/components/schemas/Base/properties/created_at"),
 *   @OA\Property(property="updatedAt", ref="#/components/schemas/Base/properties/updated_at"),
 * )
 * *
 * Class UserSite
 * @package App\Models
 * @method static create(array|int[] $params)
 * @method static findOrFail(int $solution_id)
 * @method static find($id)
 */

class UserSite extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    protected $fillable = ['user_id', 'solution_id', 'type', 'name', 'url', 'solution', 'solution_user_id', 'apikey'];
    protected $hidden = ['deleted_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function solution(): BelongsTo
    {
        return $this->belongsTo(Solution::class);
    }

    public function actionLogs(): MorphMany
    {
        return $this->morphMany(ActionLog::class, 'loggable');
    }
}
