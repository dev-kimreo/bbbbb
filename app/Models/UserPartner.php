<?php

namespace App\Models;

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
 *   schema="UserPartner",
 *   @OA\Property(property="id", type="integer", example=12),
 *   @OA\Property(property="user_id", type="integer", description="사용자의 고유번호(PK)", example=27),
 *   @OA\Property(property="name", type="string", maxLength=32, description="파트너 명", example="J맨즈 컬렉션"),
 *   @OA\Property(property="createdAt", ref="#/components/schemas/Base/properties/created_at"),
 *   @OA\Property(property="updatedAt", ref="#/components/schemas/Base/properties/updated_at"),
 * )
 * *
 * Class UserPartner
 * @package App\Models
 */

class UserPartner extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    protected $fillable = ['user_id', 'name'];
    protected $hidden = ['deleted_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actionLogs(): MorphMany
    {
        return $this->morphMany(ActionLog::class, 'loggable');
    }
}
