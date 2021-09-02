<?php

namespace App\Models\Widgets;

use App\Models\Traits\DateFormatISO8601;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WidgetUsage extends Model
{
    use HasFactory, DateFormatISO8601;

    public $timestamps = false;
    protected $fillable = ['user_id', 'widget_id', 'sort'];
    protected $hidden = ['user_id'];
    protected $casts = [
        'created_at' => 'datetime'
    ];
    protected $appends = [];
    protected $with = ['widget'];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->setAttribute('created_at', $model->freshTimestamp());
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->simplify('user');
    }

    public function widget(): BelongsTo
    {
        return $this->belongsTo(Widget::class);
    }
}
