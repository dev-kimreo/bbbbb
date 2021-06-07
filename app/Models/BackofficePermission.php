<?php

namespace App\Models;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 * @OA\Schema(
 *      @OA\Property(property="id", type="integer", example=1, description="메뉴 고유번호"),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="등록 일자"),
 *      @OA\Property(property="updatedAt", type="string", format="date-time", description="수정 일자"),
 *      @OA\Property(property="deletedAt", type="string", format="date-time", description="삭제 일자")
 * )
 *
 */
class BackofficePermission extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    protected $fillable = [
        'authority_id', 'backoffice_menu_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

}

