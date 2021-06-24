<?php

namespace App\Models\Exhibitions;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExhibitionCategory extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    protected $fillable = ['name', 'url', 'division', 'site', 'max', 'enable'];
    protected $hidden = ['deleted_at'];
    protected $casts = [
        'enable' => 'boolean'
    ];
    protected $appends = [];

    public static array $divisions = [
        'popup', 'banner'
    ];
    public static array $sites = [
        '서비스안내', '헬프센터', '어드민', '백오피스'
    ];

    public function exhibition(): HasMany
    {
        return $this->hasMany(Exhibition::class);
    }

    public function scopeSimplify($query)
    {
        return $query->select('id', 'name');
    }
}
