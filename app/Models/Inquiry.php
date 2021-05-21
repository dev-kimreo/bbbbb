<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;


/**
 *
 *  @OA\Schema(
 *      @OA\Property(property="id", type="integer", example=1, description="고유 번호" ),
 *      @OA\Property(property="userId", type="integer", example=1, description="작성자 고유번호" ),
 *      @OA\Property(property="userName", type="string", example="홍길동", description="작성자 이름" ),
 *      @OA\Property(property="title", type="string", example="1:1 문의 제목", description="1:1문의 제목" ),
 *      @OA\Property(property="question", type="string", example="1:1 문의 내용", description="1:1문의 내용" ),
 *      @OA\Property(property="status", type="string", example="waiting", description="처리상태<br/>waiting:접수<br/>answering:확인중<br/>answered:완료" ),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="등록 일자", readOnly="true"),
 *      @OA\Property(property="updatedAt", type="string", format="date-time", description="수정 일자", readOnly="true")
 *  )
 */
class Inquiry extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = false;

    public $status = [
        'waiting',       // 접수
        'answering',    // 확인중
        'answered',     // 완료
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at'
    ];

    protected $appends = [
    ];

    protected $casts = [
    ];

    // 파일 첨부 갯수 제한
    public function getAttachFileLimit() {
        return 10;
    }

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->select(['id', 'name']);
    }

    public function answer()
    {
        return $this->hasOne('App\Models\InquiryAnswer');
    }

    public function attachFiles()
    {
        return $this->morphMany('App\Models\AttachFile', 'attachable');
    }

    public function getCreatedAtAttribute($value){
        return Carbon::parse($value)->format('c');
    }

    public function getUpdatedAtAttribute($value){
        return $value ? Carbon::parse($value)->format('c') : $value;
    }
}
