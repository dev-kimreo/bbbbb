<?php

namespace App\Models\Components;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 * @OA\Schema(
 * )
 *
 */
class ComponentType extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601;


    protected $fillable = [
        'name', 'isPlural', 'hasOption', 'maxCount', 'attributes'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];


    protected $casts = [
        'attributes' => 'array'
    ];

    public function properties(): HasMany
    {
        return $this->hasMany(ComponentTypeProperty::class, 'component_type_id', 'id');
    }




}

