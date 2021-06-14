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
 *  @OA\Schema(
 *      schema="Board",
 *      @OA\Property(property="id", type="string", example=1, description="게시판 고유 번호" ),
 *      @OA\Property(property="userId", type="integer", example=1, description="게시판 생성 회원 고유번호" ),
 *      @OA\Property(property="name", type="string", example="공지사항", description="게시판 명" ),
 *      @OA\Property(property="options", type="object", format="json", description="옵션", ref="#/components/schemas/BoardOptionJson/properties/options"),
 *      @OA\Property(property="sort", type="integer", example=100, description="게시판 전시 순서" ),
 *      @OA\Property(property="enable", type="string", example="0", description="게시판 사용여부<br/>0:미사용, 1:사용" ),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="등록 일자", readOnly="true"),
 *      @OA\Property(property="updatedAt", type="string", format="date-time", description="수정 일자", readOnly="true"),
 *      @OA\Property(property="user", type="object", ref="#/components/schemas/UserSimply"),
 *      @OA\Property(property="backofficeLogs", type="array", @OA\Items(ref="#/components/schemas/BackofficeLog")),
 *  )
 *
 *  @OA\Schema(
 *      schema="boardOnList",
 *      @OA\Property(property="id", type="string", example=1, description="게시판 고유 번호" ),
 *      @OA\Property(property="userId", type="integer", example=1, description="게시판 생성 회원 고유번호" ),
 *      @OA\Property(property="name", type="string", example="공지사항", description="게시판 명" ),
 *      @OA\Property(property="options", type="object", format="json", description="옵션", ref="#/components/schemas/BoardOptionJson/properties/options"),
 *      @OA\Property(property="sort", type="integer", example=100, description="게시판 전시 순서" ),
 *      @OA\Property(property="enable", type="string", example="0", description="게시판 사용여부<br/>0:미사용, 1:사용" ),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="등록 일자", readOnly="true"),
 *      @OA\Property(property="updatedAt", type="string", format="date-time", description="수정 일자", readOnly="true"),
 *      @OA\Property(property="postsCount", type="integer", description="게시글 수"),
 *      @OA\Property(property="user", type="object", ref="#/components/schemas/UserSimply"),
 *  )
 *
 *  @OA\Schema(
 *      schema="BoardRelated",
 *      @OA\Property(property="id", type="string", example=1, description="게시판 고유 번호" ),
 *      @OA\Property(property="userId", type="integer", example=1, description="게시판 생성 회원 고유번호" ),
 *      @OA\Property(property="name", type="string", example="공지사항", description="게시판 명" ),
 *      @OA\Property(property="options", type="object", format="json", description="옵션", ref="#/components/schemas/BoardOptionJson/properties/options"),
 *      @OA\Property(property="sort", type="integer", example=100, description="게시판 전시 순서" ),
 *      @OA\Property(property="enable", type="string", example="0", description="게시판 사용여부<br/>0:미사용, 1:사용" ),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="등록 일자", readOnly="true"),
 *      @OA\Property(property="updatedAt", type="string", format="date-time", description="수정 일자", readOnly="true"),
 *  )
 *
 * Class Board
 *
 */
class Board extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'options',
        'sort',
        'enable'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['deleted_at'];

    protected $casts = [
        'options' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->simplify('manager');
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function backofficeLogs(): MorphMany
    {
        return $this->morphMany(BackofficeLog::class, 'loggable')
            ->orderByDesc('id');
    }
}
