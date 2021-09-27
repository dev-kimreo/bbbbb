<?php

namespace App\Models\EditablePages;

use App\Models\SupportedEditablePage;
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
class EditablePage extends Model
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


    public function supportedEditablePage(): BelongsTo
    {
        return $this->belongsTo(SupportedEditablePage::class, 'supported_editable_page_id');
    }

    public function editablePageLayout(): HasOne
    {
        return $this->hasOne(EditablePageLayout::class);
    }

}

