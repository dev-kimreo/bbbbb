<?php

namespace App\Models;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 * @OA\Schema(
 *      @OA\Property(property="id", type="integer", example=1, description="메뉴 고유번호"),
 *      @OA\Property(property="name", type="string", example="대시보드", description="메뉴 명"),
 *      @OA\Property(property="depth", type="integer", example=1, description="메뉴의 Depth"),
 *      @OA\Property(property="parent", type="integer", example=0, description="메뉴의 상위 메뉴 고유번호 (0:상위 없음)"),
 *      @OA\Property(property="last", type="boolean", example=1, description="메뉴의 끝 분류 여부 (0: 하위메뉴 존재, 1: 해당 메뉴가 마지막 분류)"),
 *      @OA\Property(property="sort", type="integer", example=1, description="메뉴 순서"),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="등록 일자"),
 *      @OA\Property(property="updatedAt", type="string", format="date-time", description="수정 일자"),
 *      @OA\Property(property="deletedAt", type="string", format="date-time", description="삭제 일자")
 * )
 *
 */
class BackofficeMenu extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;


    protected $fillable = [
        'name', 'depth', 'parent', 'last', 'sort'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function children(): HasMany
    {
        return $this->hasMany('App\Models\BackofficeMenu', 'parent', 'id');
    }

    public function parentMenu(): HasOne
    {
        return $this->hasOne('App\Models\BackofficeMenu', 'id', 'parent');
    }
}

