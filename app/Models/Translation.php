<?php

namespace App\Models;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *  @OA\Schema(
 *      schema="Translation",
 *      @OA\Property(property="id", type="integer", example=1, description="언어별 콘텐츠 고유번호"),
 *      @OA\Property(property="trainslationContents", type="object", description="언어별 내용",
 *          @OA\Property(property="lang", type="string", example="en", description="언어코드(ISO 639-1)" ),
 *          @OA\Property(property="value", type="string", example="Insert a number between 1 and 100", description="입력내용" ),
 *      )
 *  )
 *
 * Class Translation
 * @package App\Models
 */
class Translation extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    protected $fillable = ['linkable_type', 'linkable_id'];
    protected $with = ['translationContents'];
    protected $hidden = ['linkable_type', 'linkable_id', 'created_at', 'updated_at', 'deleted_at'];

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($translation) {
            $translation->translationContents()->each(function($o){
                $o->delete();
            });
        });
    }

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    public function translationContents(): HasMany
    {
        return $this->hasMany(TranslationContent::class);
    }
}
