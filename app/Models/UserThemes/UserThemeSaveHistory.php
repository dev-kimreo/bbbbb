<?php

namespace App\Models\UserThemes;

use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserThemeSaveHistory extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601;

    protected $fillable = ['user_theme_id', 'data'];
    protected $hidden = ['deleted_at'];
    protected $casts = [
        'data' => 'array',
        'created_at' => 'datetime'
    ];

    public function userTheme(): BelongsTo
    {
        return $this->belongsTo(UserTheme::class);
    }
}
