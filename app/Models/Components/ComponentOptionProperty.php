<?php

namespace App\Models\Components;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 * @OA\Schema(
 * )
 *
 */
class ComponentOptionProperty extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601;

    public static string $exceptionEntity = "componentOptionProperty";

    protected $fillable = [
        'component_option_id', 'component_type_property_id', 'key', 'name', 'initial_value', 'elements'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];
    protected $casts = ['elements' => 'array'];

    public function property(): belongsTo
    {
        return $this->belongsTo(ComponentTypeProperty::class, 'component_type_property_id', 'id');
    }

    public function option(): belongsTo
    {
        return $this->belongsTo(ComponentOption::class, 'component_option_id', 'id');
    }
}

