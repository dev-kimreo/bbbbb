<?php

namespace App\Models;

use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLoginLog extends Model
{
    use HasFactory, DateFormatISO8601;

    public $timestamps = false;
    protected $fillable = ['user_id', 'manager_id', 'client_id', 'ip'];
    protected $hidden = ['user_id'];
    protected $with = ['user'];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = $model->created_at ?? $model->freshTimestamp();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->simplify();
    }
}
