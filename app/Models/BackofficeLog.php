<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @OA\Schema (
 *   schema="BackofficeLog",
 *   @OA\Property(property="id", type="integer", example="22"),
 *   @OA\Property(property="memo", type="string", example="정보수정"),
 *   @OA\Property(property="createdAt", ref="#/components/schemas/Base/properties/created_at"),
 *   @OA\Property(property="user", type="object", ref="#/components/schemas/UserSimply")
 * )
 *
 * Class BackofficeLog
 * @package App\Models
 */
class BackofficeLog extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $hidden = ['user_id', 'loggable_type', 'loggable_id'];
    public $with = ['user'];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = $model->created_at ?? $model->freshTimestamp();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->select(['id', 'name', 'email']);
    }

    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }
}
