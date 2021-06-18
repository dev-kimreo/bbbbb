<?php

namespace App\Models;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 * @OA\Schema(
 * @OA\Property(property="id", type="integer", readOnly="true", example="1"),
 * @OA\Property(property="code", type="string", readOnly="true", description="그룹번호", example="13"),
 * @OA\Property(property="title", type="string", readOnly="true", description="그룹명", example="시스템관리자"),
 * @OA\Property(
 *     property="displayName", type="string", readOnly="true",
 *     description="닉네임(그룹명과 별개로 일반 사용자에게 표시할 이름)", example="운영자"
 * ),
 * @OA\Property(property="memo", type="string", maxLength=255, description="설명", example="큐픽 사이트 운영"),
 * @OA\Property(
 *     property="createdAt", type="string", readOnly="true", format="date-time",
 *     ref="#/components/schemas/Base/properties/created_at", example="2021-02-25 12:59:20"
 * ),
 * @OA\Property(
 *     property="updatedAt", type="string", readOnly="true", format="date-time",
 *     ref="#/components/schemas/Base/properties/updated_at", example="2021-02-25 12:59:20"
 * ),
 * @OA\Property(
 *     property="deletedAt", type="string", readOnly="true", format="date-time",
 *     ref="#/components/schemas/Base/properties/updated_at", example="2021-02-25 13:12:47"
 * )
 * )
 *
 * Class Authority
 *
 */
class Authority extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'title',
        'display_name',
        'memo'
    ];

    public function managers(): HasMany
    {
        return $this->hasMany(Manager::class);
    }

    public function permissions()
    {
        return $this->hasMany(BackofficePermission::class);
    }
}
