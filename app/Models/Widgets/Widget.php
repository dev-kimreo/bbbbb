<?php

namespace App\Models\Widgets;

use App\Models\ActionLog;
use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *  @OA\Schema(
 *      schema="Widget",
 *      @OA\Property(property="id", type="integer", example=1, description="위젯 고유번호" ),
 *      @OA\Property(property="name", type="string", example="최근 사용한 테마", description="위젯명" ),
 *      @OA\Property(property="description", type="string", example="로그인한 회원이 편집한 테마를 편집일 역순으로 표시", description="위젯 기능설명" ),
 *      @OA\Property(property="enable", type="boolean", example="true", description="사용구분<br/>true:사용, false:미사용" ),
 *      @OA\Property(property="onlyForManager", type="boolean", example="false", description="관리자 전용 위젯 여부<br/>true:관리자전용, false:모든 사용자용" ),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="등록일자", readOnly="true"),
 *      @OA\Property(property="updatedAt", type="string", format="date-time", description="수정일자", readOnly="true"),
 *      @OA\Property(property="creator", type="object", ref="#/components/schemas/UserSimply")
 *  )
 *
 * @method static findOrFail(int $id)
 * @method static create(array $array_merge)
 * @method static orderByDesc(string $string)
 */
class Widget extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    protected $fillable = ['user_id', 'name', 'description', 'enable', 'only_for_manager'];
    protected $hidden = ['deleted_at', 'user_id'];
    protected $casts = [
        'enable' => 'boolean',
        'only_for_manager' => 'boolean'
    ];
    protected $appends = [];
    protected $with = ['creator'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->simplify('manager');
    }

    public function usage(): HasMany
    {
        return $this->hasMany(WidgetUsage::class);
    }

    public function backofficeLogs(): MorphMany
    {
        return $this->morphMany(ActionLog::class, 'loggable')->forBackoffice();
    }
}
