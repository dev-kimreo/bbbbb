<?php

namespace App\Models\LinkedComponents;

use App\Models\EditablePages\EditablePageLayout;
use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 * @OA\Schema(
 * )
 *
 */
class LinkedComponentGroup extends Model
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

    public function usedForHeader(): HasMany
    {
        return $this->hasMany(EditablePageLayout::class, 'header_component_group_id', 'id');
    }

    public function usedForContent(): HasOne
    {
        return $this->hasOne(EditablePageLayout::class, 'content_component_group_id', 'id');
    }

    public function usedForFooter(): HasMany
    {
        return $this->hasMany(EditablePageLayout::class, 'footer_component_group_id', 'id');
    }

    public function linkedComponent(): HasMany
    {
        return $this->hasMany(LinkedComponent::class, 'linked_component_group_id', 'id');
    }
}

