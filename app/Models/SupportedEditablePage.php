<?php

namespace App\Models;

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
 * @method static find(mixed $supportedEditablePageId)
 */
class SupportedEditablePage extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;


    public static string $exceptionEntity = "supportedEditablePage";

    protected $fillable = [
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];


    public function solution(): BelongsTo
    {
        return $this->belongsTo(Solution::class);
    }

}

