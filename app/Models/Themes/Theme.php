<?php

namespace App\Models\Themes;

use App\Models\EditablePages\EditablePage;
use App\Models\Solution;
use App\Models\Traits\DateFormatISO8601;
use App\Models\Users\User;
use App\Models\UserThemes\UserThemePurchaseHistory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 *
 * @OA\Schema(
 *      @OA\Property(property="id", type="integer", example=1, description="고유번호"),
 *      @OA\Property(property="theme_product_id", type="integer", example=1, description="테마 상품 고유 번호"),
 *      @OA\Property(property="solution_id", type="integer", example=1, description="솔루션 고유 번호"),
 *      @OA\Property(property="status", type="string", example="making", description="테마 상태<br/>making: 제작 중, underReview: 심사 중, reviewCompleted: 심사 완료"),
 *      @OA\Property(property="display", type="boolean", example=false, default=false, description="노출 여부"),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="등록 일자"),
 *      @OA\Property(property="updatedAt", type="string", format="date-time", description="수정 일자")
 * )
 *
 * @method where(array $array)
 * @method static findOrFail(int $theme_id)
 * @method static find(int $theme_id)
 * @property mixed editablePages
 */
class Theme extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601;

    public static string $exceptionEntity = "theme";

    protected $fillable = ['theme_product_id', 'solution_id', 'status', 'display'];

    public static array $status = [
        'making',           // 제작 중
        'underReview',      // 심사 중
        'reviewCompleted'   // 심사 완료
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function product(): BelongsTo
    {
        return $this->belongsTo(ThemeProduct::class, 'theme_product_id');
    }

    public function solution(): BelongsTo
    {
        return $this->belongsTo(Solution::class, 'solution_id');
    }

    public function editablePages(): HasMany
    {
        return $this->hasMany(EditablePage::class);
    }

    public function purchasingUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, UserThemePurchaseHistory::query()->getModel()->getTable());
    }
}
