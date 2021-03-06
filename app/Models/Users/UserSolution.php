<?php

namespace App\Models\Users;

use App\Models\ActionLog;
use App\Models\Solution;
use App\Models\Traits\DateFormatISO8601;
use App\Models\Traits\CheckUpdatedAt;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 * @OA\Schema(
 *   schema="UserSolution",
 *   @OA\Property(property="id", type="integer", example="12"),
 *   @OA\Property(property="user_id", type="integer", description="사용자의 고유번호(PK)", example="27"),
 *   @OA\Property(property="solution_id", type="integer", description="솔루션 고유번호(PK)", example="3"),
 *   @OA\Property(property="solutionName", type="string", maxLength=16, description="연동된 솔루션명", example="마이소호"),
 *   @OA\Property(property="solutionUserId", type="string", maxLength=128, description="연동된 솔루션 회원 ID", example="honggildong"),
 *   @OA\Property(property="apikey", type="string", maxLength=512, description="연동된 솔루션의 API Key", example="apikey31f7sdg6bsd73"),
 *   @OA\Property(property="createdAt", ref="#/components/schemas/Base/properties/created_at"),
 *   @OA\Property(property="updatedAt", ref="#/components/schemas/Base/properties/updated_at"),
 * )
 *
 * @OA\Schema(
 *   schema="UserSolutionWithRelation",
 *   @OA\Property(property="id", type="integer", example="12"),
 *   @OA\Property(property="user_id", type="integer", description="사용자의 고유번호(PK)", example="27"),
 *   @OA\Property(property="solution_id", type="integer", description="솔루션 고유번호(PK)", example="3"),
 *   @OA\Property(property="solutionName", type="string", maxLength=16, description="연동된 솔루션명", example="마이소호"),
 *   @OA\Property(property="solutionUserId", type="string", maxLength=128, description="연동된 솔루션 회원 ID", example="honggildong"),
 *   @OA\Property(property="apikey", type="string", maxLength=512, description="연동된 솔루션의 API Key", example="apikey31f7sdg6bsd73"),
 *   @OA\Property(property="createdAt", ref="#/components/schemas/Base/properties/created_at"),
 *   @OA\Property(property="updatedAt", ref="#/components/schemas/Base/properties/updated_at"),
 *   @OA\Property(property="solution", ref="#/components/schemas/Solution")
 * )
 * *
 * Class UserSolution
 * @package App\Models
 * @method static create(array|int[] $params)
 * @method static findOrFail(int $solution_id)
 * @method static find($id)
 */

class UserSolution extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    public static string $exceptionEntity = "userSolution";

    protected $fillable = ['user_id', 'solution_id', 'solution_user_id', 'apikey'];
    protected $hidden = ['deleted_at'];
    protected $appends = ['solution_name'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function solution(): BelongsTo
    {
        return $this->belongsTo(Solution::class);
    }

    public function sites(): HasMany
    {
        return $this->hasMany(UserSite::class);
    }

    public function actionLogs(): MorphMany
    {
        return $this->morphMany(ActionLog::class, 'loggable');
    }

    public function getSolutionNameAttribute()
    {
        return $this->solution()->first()->name;
    }
}
