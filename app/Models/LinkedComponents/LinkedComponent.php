<?php

namespace App\Models\LinkedComponents;

use App\Models\Components\Component;
use App\Models\Traits\CheckUpdatedAt;
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
 */
class LinkedComponent extends Model
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


    public function component(): BelongsTo
    {
        return $this->belongsTo(Component::class, 'component_id');
    }

    public function linkedOption(): HasMany
    {
        return $this->hasMany(LinkedComponentOption::class, 'linked_component_id', 'id');
    }
}

