<?php

namespace App\Models\Themes;

use App\Models\Traits\DateFormatISO8601;
use App\Models\Users\UserPartner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 * @OA\Schema(
 *      @OA\Property(property="id", type="integer", example=1, description="고유번호"),
 *      @OA\Property(property="user_partner_id", type="integer", example=1, description="파트너 회원 고유 번호"),
 *      @OA\Property(property="name", type="string", example="테마 상품", description="테마 상품명"),
 *      @OA\Property(property="all_usable", type="boolean", example=false, default=false, description="모든 사용자에게 제공 여부"),
 *      @OA\Property(property="display", type="boolean", example=false, default=false, description="전시 여부"),
 *      @OA\Property(property="show_me_only", type="boolean", example=true, default=false, description="본인에게만 노출 여부"),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="등록 일자"),
 *      @OA\Property(property="updatedAt", type="string", format="date-time", description="수정 일자")
 * )
 *
 * @method where(array $array)
 * @method findOrFail($theme_product_id)
 * @method static create(array $array_merge)
 */
class ThemeProduct extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601;


    protected $fillable = [
        'user_partner_id', 'name', 'all_usable', 'display', 'show_me_only'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['deleted_at'];


    public function creator(): BelongsTo
    {
        return $this->belongsTo(UserPartner::class, 'user_partner_id');
    }

    public function theme(): HasMany
    {
        return $this->hasMany(Theme::class);
    }

    public function themeInformation(): HasOne
    {
        return $this->hasOne(ThemeProductInformation::class);
    }


}

