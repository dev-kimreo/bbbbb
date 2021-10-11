<?php

namespace App\Models\EditablePages;

use App\Models\LinkedComponents\LinkedComponentGroup;
use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 * @OA\Schema(
 * )
 *
 */
class EditablePageLayout extends Model
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

    public function linkedHeaderComponentGroup(): BelongsTo
    {
        return $this->belongsTo(LinkedComponentGroup::class, 'header_component_group_id');
    }

    public function linkedContentComponentGroup(): BelongsTo
    {
        return $this->belongsTo(LinkedComponentGroup::class, 'content_component_group_id');
    }

    public function linkedFooterComponentGroup(): BelongsTo
    {
        return $this->belongsTo(LinkedComponentGroup::class, 'footer_component_group_id');
    }
}

