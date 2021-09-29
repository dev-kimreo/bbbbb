<?php

namespace App\Models\Themes;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use App\Models\Users\UserPartner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 * @OA\Schema(
 * )
 *
 */
class ThemeProduct extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;


    protected $fillable = [
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];


    public function creator(): BelongsTo
    {
        return $this->belongsTo(UserPartner::class, 'user_partner_id');
    }

    public function theme(): HasMany
    {
        return $this->hasMany(Theme::class);
    }

    public function themeInformation(): HasOne
    {
        return $this->hasOne(ThemeProductInformation::class);
    }


}

