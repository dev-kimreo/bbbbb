<?php

namespace App\Models\UserThemes;

use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *   schema="UserThemeSaveHistory",
 *   @OA\Property(property="id", type="integer", readOnly="true", example="1"),
 *   @OA\Property(property="user_theme_id", type="integer", description="회원 테마의 고유번호(PK)", example=27),
 *   @OA\Property(property="data", type="json",  description="회원 테마 저장데이터 (JSON Format)"),
 *   @OA\Property(property="createdAt", ref="#/components/schemas/Base/properties/created_at"),
 * )
 */
class UserThemeSaveHistory extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601;

    public $timestamps = false;
    protected $fillable = ['user_theme_id', 'data'];
    protected $hidden = ['deleted_at'];
    protected $casts = [
        'data' => 'array',
        'created_at' => 'datetime'
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->setAttribute('created_at', $model->freshTimestamp());
        });
    }

    public function userTheme(): BelongsTo
    {
        return $this->belongsTo(UserTheme::class);
    }
}
