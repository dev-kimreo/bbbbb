<?php

namespace App\Models;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;


/**
 *
 *  @OA\Schema(
 *      schema="Post",
 *      @OA\Property(property="id", type="integer", example=1, description="게시글 고유번호" ),
 *      @OA\Property(property="boardId", type="integer", example=1, description="게시판 고유번호" ),
 *      @OA\Property(property="userId", type="integer", example=1, description="작성자 회원 고유번호" ),
 *      @OA\Property(property="title", type="string", example="게시글 제목입니다.", description="게시글 제목" ),
 *      @OA\Property(property="content", type="string", example="게시글 내용입니다.", description="게시글 내용" ),
 *      @OA\Property(property="hidden", type="integer", example=0, default=0, description="게시글 숨김 여부<br/>0:노출<br/>1:숨김" ),
 *      @OA\Property(property="sort", type="integer", example=100, description="게시판 전시 순서" ),
 *      @OA\Property(property="createdAt", type="datetime", example="2021-04-08T07:04:52+00:00", description="게시글 작성일자" ),
 *      @OA\Property(property="updatedAt", type="datetime", example="2021-04-08T07:57:55+00:00", description="게시글 수정일자" ),
 *      @OA\Property(property="thumbnail", type="object", description="게시글 섬네일 이미지 정보",
 *          @OA\Property(property="id", type="integer", example=4, description="이미지 고유 번호" ),
 *          @OA\Property(property="url", type="string", example="http://local-api.qpicki.com/storage/post/048/000/000/caf4df2767fea15158143aaab145d94e.jpg", description="이미지 url" ),
 *      ),
 *      @OA\Property(property="attachFiles", type="object", ref="#/components/schemas/AttachFile"),
 *      @OA\Property(property="user", type="object", ref="#/components/schemas/UserSimply"),
 *      @OA\Property(property="board", type="object", ref="#/components/schemas/BoardRelated"),
 *      @OA\Property(property="backofficeLogs", type="array", @OA\Items(ref="#/components/schemas/BackofficeLog")),
 *  )
 *
 * Class Post
 *
 */
class Post extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'board_id',
        'user_id',
        'title',
        'content',
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['deleted_at'];

    protected $appends = [
    ];

    protected $casts = [
        'etc' => 'array'
    ];

    public function scopeId($q, $id)
    {
        return $q->where('id', $id);
    }

    public function scopeBoardId($q, $boardId)
    {
        return $q->where('board_id', $boardId);
    }

    public function getByBoardId($id, $boardId)
    {
        return $this->id($id)->boardId($boardId);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->simplify();
    }

    public function replies()
    {
        return $this->hasMany('App\Models\Reply');
    }

    public function attachFiles()
    {
        return $this->morphMany('App\Models\AttachFile', 'attachable');
    }

    public function thumbnail()
    {
        return $this->hasOne('App\Models\PostThumbnail');
    }

    public function board()
    {
        return $this->belongsTo('App\Models\Board', 'board_id', 'id');
    }

    public function backofficeLogs(): MorphMany
    {
        return $this->morphMany(BackofficeLog::class, 'loggable')
            ->orderByDesc('id');
    }

    public function getAttachFileLimit(): int
    {
        return intval($this->board->options['attach_limit']);
    }

    public function checkAttachableModel(): bool
    {
        return intval($this->board->options['attach']) ? true : false;
    }
}
