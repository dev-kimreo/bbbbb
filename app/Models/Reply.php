<?php

namespace App\Models;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *  @OA\Schema (
 *      schema="Reply",
 *      @OA\Property(property="id", type="integer", example=23, description="댓글 고유번호"),
 *      @OA\Property(property="postId", type="integer", example=175, description="게시글 고유번호"),
 *      @OA\Property(property="content", type="string", example="댓글 내용입니다.", description="댓글 내용" ),
 *      @OA\Property(property="hidden", type="boolean", example="false", description="노출 또는 숨김 여부<br/>false:노출<br/>true:숨김"),
 *      @OA\Property(property="createdAt", type="datetime", example="2021-04-08T07:04:52+00:00", description="작성일자" ),
 *      @OA\Property(property="updatedAt", type="datetime", example="2021-04-08T07:57:55+00:00", description="수정일자" ),
 *      @OA\Property(property="user", type="object", ref="#/components/schemas/UserSimply"),
 *  )
 *
 * Class Reply
 * @package App\Models
 * @method static where(string $string, $id)
 * @method static findOrFail($id)
 */
class Reply extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['content', 'post_id', 'user_id'];
    protected $hidden = ['user_id', 'deleted_at'];
    protected $appends = [];
    protected $casts = [
        'hidden' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->simplify('manager');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
