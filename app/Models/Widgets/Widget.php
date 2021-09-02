<?php

namespace App\Models\Widgets;

use App\Models\ActionLog;
use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method static findOrFail(int $id)
 * @method static create(array $array_merge)
 */
class Widget extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    protected $fillable = ['user_id', 'name', 'description', 'enable', 'only_for_manager'];
    protected $hidden = ['deleted_at', 'user_id'];
    protected $casts = [
        'enable' => 'boolean',
        'only_for_manager' => 'boolean'
    ];
    protected $appends = [];
    protected $with = ['creator'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->simplify('manager');
    }

    public function usage(): HasMany
    {
        return $this->hasMany(WidgetUsage::class);
    }

    public function backofficeLogs(): MorphMany
    {
        return $this->morphMany(ActionLog::class, 'loggable')->forBackoffice();
    }
}
