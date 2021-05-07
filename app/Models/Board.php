<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;



/**
 *
 *  @OA\Schema(
 *      @OA\Xml(name="게시판"),
 *      @OA\Property(property="id", type="string", example=1, description="게시판 고유 번호" ),
 *      @OA\Property(property="name", type="string", example="공지사항", description="게시판 명" ),
 *      @OA\Property(property="type", type="string", example="notice", description="게시판 타입" ),
 *      @OA\Property(property="hidden", type="string", example="0", description="게시판 숨김여부<br/>0:노출, 1:숨김" ),
 *  )
 *
 * Class Board
 *
 */
class Board extends Model
{
    use HasFactory;


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
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'options' => 'array'
    ];


    public function getCreatedAtAttribute($value){
        return Carbon::parse($value)->format('c');
    }

    public function getUpdatedAtAttribute($value){
        return Carbon::parse($value)->format('c');
    }

}
