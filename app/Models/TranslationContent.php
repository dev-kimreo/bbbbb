<?php

namespace App\Models;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TranslationContent extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    protected $fillable = ['lang', 'value'];
    protected $hidden = ['id', 'translation_id', 'created_at', 'updated_at', 'deleted_at'];

    public function word(): BelongsTo
    {
        return $this->belongsTo(Translation::class);
    }

    public function scopeSimplify($query)
    {
        return $query->select(['lang', 'value']);
    }
}
