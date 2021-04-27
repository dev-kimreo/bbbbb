<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;
use Carbon\Carbon;


/**
 *
 *  @OA\Schema(
 *      @OA\Xml(name="이미지"),
 *      @OA\Property(property="id", type="integer", example=1, description="업로드 파일 고유 번호" ),
 *      @OA\Property(property="server", type="string", example="public", description="업로드된 서버" ),
 *      @OA\Property(property="type", type="string", example="temp", description=" 업로드 타입" ),
 *      @OA\Property(property="type_id", type="integer", example=1, description="타입의 고유 번호" ),
 *      @OA\Property(property="user_id", type="integer", example=1, description="회원 고유 번호" ),
 *      @OA\Property(property="url", type="integer", example=1, description="회원 고유 번호" ),
 *      @OA\Property(property="path", type="integer", example=1, description="회원 고유 번호" ),
 *      @OA\Property(property="name", type="integer", example=1, description="회원 고유 번호" ),
 *      @OA\Property(property="sort", type="string", example="0", description="게시판 옵션 순서 " ),
 *  )
 *
 * Class BoardOption
 *
 */
class AttachFile extends Model
{
    use HasFactory, Eloquence, Mappable;

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
    ];

    protected $casts = [
    ];

    protected $maps = [
    ];

    protected $appends = [
    ];


    public function getCreatedAtAttribute($value){
        return Carbon::parse($value)->format('c');
    }

    public function getUpdatedAtAttribute($value){
        return Carbon::parse($value)->format('c');
    }

}
