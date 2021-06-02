<?php

namespace App\Models;

use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAdvAgree extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601;

    public $timestamps = false;
    protected $table = 'user_advertising_agrees';
    protected $fillable = ['user_id', 'agree'];
    protected $hidden = ['id', 'user_id', 'deleted_at'];
    protected $casts = [
        'agree' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->setAttribute('created_at', $model->freshTimestamp());
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function setUpdatedAt($value)
    {
        // Do nothing
    }
}
