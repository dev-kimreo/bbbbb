<?php

namespace App\Models;

use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;


/**
 *
 *  @OA\Schema(
 *      @OA\Property(property="id", type="integer", example=1, description="게시글 고유 번호" ),
 *      @OA\Property(property="boardId", type="integer", example=7, description="게시판 고유 번호" ),
 *      @OA\Property(property="userId", type="integer", example=1, description="게시글 생성 회원 고유번호" ),
 *      @OA\Property(property="title", type="string", example="게시글 제목입니다.", description="게시판 제목" ),
 *      @OA\Property(property="content", type="string", example="게시글 내용입니다.", description="게시판 내용" ),
 *      @OA\Property(property="hidden", type="boolean", example=0, description="게시글 숨김 여부" ),
 *      @OA\Property(property="sort", type="integer", example=100, description="게시판 전시 순서" ),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="등록 일자"),
 *      @OA\Property(property="updatedAt", type="string", format="date-time", description="수정 일자"),
 *      @OA\Property(property="deletedAt", type="string", format="date-time", description="삭제 일자")
 *  )
 *
 * Class Post
 *
 */
class Post extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601;

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
    protected $hidden = [
    ];

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
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->select(['id', 'name']);
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

    public function getAttachFileLimit(): int
    {
        return intval($this->board->options['attach_limit']);
    }

    public function checkAttachableModel(): bool
    {
        return intval($this->board->options['attach']) ? true : false;
    }


}
