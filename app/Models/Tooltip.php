<?php

namespace App\Models;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tooltip extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    protected $appends = ['code'];
    protected $fillable = ['user_id', 'type', 'title', 'visible'];
    protected $hidden = ['deleted_at'];
    protected $casts = [
        'visible' => 'boolean'
    ];

    public static array $prefixes = [
        'SV' => '서비스소개',
        'HP' => '헬프센터',
        'AD' => '어드민',
        'BO' => '백오피스',
        'PL' => '플러그인',
        'ST' => '스토어',
        'PT' => '파트너센터'
    ];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($tooltip) {
            $tooltip->translation()->each(function($o){
                $o->delete();
            });
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->simplify();
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function translation(): MorphOne
    {
        return $this->morphOne(Translation::class, 'linkable');
    }

    public function contents(): HasMany
    {
        return $this->translation()->first()->hasMany(TranslationContent::class);
    }

    public function backofficeLogs(): MorphMany
    {
        return $this->morphMany(BackofficeLog::class, 'loggable')
            ->orderByDesc('id');
    }

    public function getCodeAttribute(): string
    {
        $prefix = collect(self::$prefixes)->search($this->attributes['type']);
        return $prefix . '_' . str_pad($this->attributes['id'], 4, '0', STR_PAD_LEFT);
    }

    public function setCodeAttribute($v)
    {
        $this->attributes['type'] = self::$prefixes[strstr($v, '_', true)];
        $this->attributes['id'] = substr(strrchr($v, "/"), 1);
    }
}
