<?php

namespace App\Models;

use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static selectRaw(string $string)
 * @method static where(string $string, int $user_id)
 */
class UserLoginLog extends Model
{
    use HasFactory, DateFormatISO8601;

    public $timestamps = false;
    protected $appends = ['attempted_user'];
    protected $fillable = ['user_id', 'user_grade', 'manager_id', 'client_id', 'ip'];
    protected $hidden = ['user_id', 'manager_id', 'client_id'];
    protected $casts = [
        'created_at' => 'datetime',
    ];
    protected $with = [];

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

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id')->simplify('manager');
    }

    public function getAttemptedUserAttribute()
    {
        if(empty($this->attributes['manager_id'])) {
            return $this->user()->first();
        } else {
            return $this->manager()->first();
        }
    }
}
