<?php

namespace App\Models;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *  @OA\Schema(
 *      schema="TranslationContentsSimply",
 *      @OA\Property(property="ko", type="string", example="한국어로 작성한 콘텐츠입니다", description="한국어 콘텐츠" ),
 *      @OA\Property(property="en", type="string", example="These contents were written in English.", description="영어 콘텐츠" ),
 *      @OA\Property(property="ISO 639-1 2자리 코드", type="string", example="Key에 표시된 언어코드로 작성된 콘텐츠", description="Key에 표시된 언어코드로 작성된 콘텐츠" ),
 *  )
 * 
 * Class TranslationContent
 * @package App\Models
 */
class TranslationContent extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    protected $fillable = ['lang', 'value'];
    protected $hidden = ['id', 'translation_id', 'created_at', 'updated_at', 'deleted_at'];

    public function word(): BelongsTo
    {
        return $this->belongsTo(Translation::class);
    }

    public function scopeSimplify($query)
    {
        return $query->select(['lang', 'value']);
    }
}
