<?php

namespace App\Models\Themes;

use App\Models\EditablePages\EditablePage;
use App\Models\Solution;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 * @OA\Schema(
 * )
 *
 * @method where(array $array)
 */
class Theme extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601;

    protected $fillable = ['theme_product_id', 'solution_id', 'status', 'display'];

    public static array $status = ['registering', 'registered'];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function solution(): BelongsTo
    {
        return $this->belongsTo(Solution::class, 'solution_id');
    }

    public function editablePage(): HasMany
    {
        return $this->hasMany(EditablePage::class);
    }

}

