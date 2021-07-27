<?php

namespace App\Models;

use App\Models\Traits\DateFormatISO8601;
use App\Models\Users\User;
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
 * @method updateOrCreate(array $array, array $data)
 */
class ActionLog extends Model
{
    use HasFactory, DateFormatISO8601;

    public $timestamps = false;
    public $fillable = [
        'client_id', 'conn_id', 'crud', 'ip', 'loggable_id', 'loggable_type',
        'memo', 'path', 'properties', 'title', 'user_id', 'user_grade'
    ];
    public $hidden = ['user_id', 'loggable_type', 'loggable_id'];
    public $with = ['user'];
    protected $casts = [
        'properties' => 'json'
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = $model->created_at ?? $model->freshTimestamp();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->simplify('user');
    }

    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForBackoffice($query)
    {
        return $query
            ->select('loggable_id', 'loggable_type', 'user_id', 'title', 'properties', 'created_at')
            ->where('client_id', '=', 2)
            ->orderByDesc('id');
    }

    public function scopeLoginLog($query, $user_id = null)
    {
        $where = [
            'title' => '로그인',
            'loggable_type' => 'user'
        ];

        if ($user_id) {
            $where['loggable_id'] = $user_id;
        }

        return $query
            ->select('id', 'ip', 'user_id', 'created_at', 'properties')
            ->where($where)
            ->orderByDesc('id');
    }

    public function scopeLoginLogStatistics($query, $start, $end)
    {
        return $query
            ->selectRaw('user_grade as grade, count(id) as count')
            ->whereBetween('created_at', [$start, $end])
            ->where([
                'title' => '로그인',
                'loggable_type' => 'user'
            ])
            ->groupBy('grade');
    }
}
