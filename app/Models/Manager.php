<?php

namespace App\Models;

use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

/**
 *
 * @OA\Schema(
 * @OA\Property(property="id", type="integer", readOnly="true", example="1"),
 * @OA\Property(property="userId", type="integer", readOnly="true", description="사용자의 고유번호(PK)", example="1"),
 * @OA\Property(property="authorityId", type="integer", readOnly="true", description="관리자그룹의 고유번호(PK)", example="1"),
 * @OA\Property(
 *     property="createdAt", type="string", readOnly="true", format="date-time",
 *     ref="#/components/schemas/Base/properties/created_at", example="2021-02-25 12:59:20"
 * ),
 * @OA\Property(property="user", type="object", readOnly="true", ref="#/components/schemas/User"),
 * @OA\Property(property="authority", type="object", readOnly="true", ref="#/components/schemas/Authority")
 * )
 *
 */
class Manager extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'updated_at', 'deleted_at'
    ];

    protected $with = [
        'user', 'authority'
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->simplify();
    }

    public function authority()
    {
        return $this->belongsTo(Authority::class);
    }
}

