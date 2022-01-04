<?php

namespace App\Models;

use App\Models\Components\Component;
use App\Models\Traits\DateFormatISO8601;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ScriptRequest extends Model
{
    use HasFactory;
    use DateFormatISO8601;

    public static string $exceptionEntity = "scriptRequest";

    public $timestamps = false;
    protected $fillable = [
        'user_id', 'hash', 'url'
    ];
    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->setAttribute('created_at', $model->freshTimestamp());
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class)->simplify('manager');
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class, 'component_id');
    }

    public function requestable(): MorphTo
    {
        return $this->morphTo();
    }
}
