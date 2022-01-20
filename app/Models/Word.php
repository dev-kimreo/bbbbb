<?php

namespace App\Models;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     @OA\Property(property="id", type="integer", example=1, description="고유번호"),
 *     @OA\Property(property="code", type="string", example="post.title", description="용어 code"),
 *     @OA\Property(property="title", type="string", example="게시글 제목", description="용어 제목"),
 *     @OA\Property(property="createdAt", type="datetime", example="2021-04-08T07:04:52+00:00", description="작성일자" ),
 *     @OA\Property(property="updatedAt", type="datetime", example="2021-04-08T07:57:55+00:00", description="수정일자" ),
 *  )
 *
 * @OA\Schema(
 *     schema="RelationWord",
 *     @OA\Property(property="id", type="integer", example=1, description="고유번호"),
 *     @OA\Property(property="code", type="string", example="common.not_found", description="용어 code"),
 *     @OA\Property(property="title", type="string", example="요청한 데이터를 찾을 수 없습니다.", description="용어 제목"),
 *     @OA\Property(property="translation", type="object", ref="#/components/schemas/Translation"),
 *     @OA\Property(property="createdAt", type="datetime", example="2021-04-08T07:04:52+00:00", description="작성일자" ),
 *     @OA\Property(property="updatedAt", type="datetime", example="2021-04-08T07:57:55+00:00", description="수정일자" ),
 *  )
 *
 **/
class Word extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    public static string $exceptionEntity = "word";
    protected $fillable = ['code', 'title'];
    protected $with = ['translation'];

    public function translation(): MorphOne
    {
        return $this->morphOne(Translation::class, 'linkable');
    }
}
