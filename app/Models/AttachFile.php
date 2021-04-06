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
 *      @OA\Xml(name="게시판 옵션"),
 *      @OA\Property(property="id", type="string", example=1, description="게시판 옵션 고유 번호" ),
 *      @OA\Property(property="name", type="string", example="글 작성", description="게시판 옵션 명" ),
 *      @OA\Property(property="type", type="string", example="board", description="게시판 옵션 타입" ),
 *      @OA\Property(property="dataType", type="string", example="radio", description="게시판 옵션 데이터 타입" ),
 *      @OA\Property(property="default", type="string", example="all", description="게시판 옵션 기본값" ),
 *      @OA\Property(property="options", type="array", collectionFormat="multi", example={{"value":"all","comment":"모두 작성 가능"},{"value":"manager","comment":"운영진만 작성 가능"},{"value":"member","comment":"회원만 작성가능"}},
 *          @OA\Items(
 *              @OA\Property(property="value", type="string", description="옵션 값" ),
 *              @OA\Property(property="comment", type="string", description="옵션 값에 대한 설명" )
 *          ),
 *      ),
 *      @OA\Property(property="sort", type="string", example="0", description="게시판 옵션 순서 " ),
 *  )
 *
 * Class BoardOption
 *
 */
class BoardOption extends Model
{
    use HasFactory, Eloquence, Mappable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'type',
        'options',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'data_type',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'options' => 'array'
    ];

    protected $maps = [
        'dataType' => 'data_type',
    ];

    protected $appends = [
        'dataType',
    ];


    public function getCreatedAtAttribute($value){
        return Carbon::parse($value)->format('c');
    }

    public function getUpdatedAtAttribute($value){
        return Carbon::parse($value)->format('c');
    }

}
