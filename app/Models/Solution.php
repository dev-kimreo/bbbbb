<?php

namespace App\Models;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use App\Models\Users\UserSolution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 * @OA\Schema(
 *      @OA\Property(property="id", type="integer", example=1, description="솔루션 고유번호"),
 *      @OA\Property(property="name", type="string", example="메이크샵", description="솔루션 명"),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="등록 일자"),
 *      @OA\Property(property="updatedAt", type="string", format="date-time", description="수정 일자")
 * )
 *
 * @method static create(array $all)
 * @method static findOrFail($solution_id)
 * @method static find($solution_id)
 * @method static where(string $string, string $string1)
 */
class Solution extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;


    public static string $exceptionEntity = "solution";

    protected $fillable = [
        'name'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['deleted_at'];

    public function supportedEditablePage(): HasMany
    {
        return $this->hasMany(SupportedEditablePage::class);
    }

    public function userSolutions(): HasMany
    {
        return $this->hasMany(UserSolution::class);
    }
}

