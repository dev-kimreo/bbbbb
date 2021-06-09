<?php

namespace App\Models;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Translation extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    protected $fillable = ['linkable_type', 'linkable_id', 'code', 'explanation'];
    protected $with = ['translationContents'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($translation) {
            $translation->translationContents()->each(function($o){
                $o->delete();
            });
        });
    }

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    public function translationContents(): HasMany
    {
        return $this->hasMany(TranslationContent::class);
    }
}
