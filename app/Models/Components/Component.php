<?php

namespace App\Models\Components;

use App\Models\Solution;
use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use App\Models\Users\UserPartner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 * @OA\Schema(
 *      @OA\Property(property="id", type="integer", example=1, description="고유번호"),
 *      @OA\Property(property="user_partner_id", type="integer", example=1, description="파트너 고유 번호"),
 *      @OA\Property(property="solution_id", type="integer", example=1, description="솔루션 고유 번호"),
 *      @OA\Property(property="name", type="string", example="컴포넌트", description="컴포넌트 명"),
 *      @OA\Property(property="use_other_than_maker", type="boolean", example=true, description="제작자 외 회원 사용가능 여부"),
 *      @OA\Property(property="first_category", type="string", example="design", description="컴포넌트 유형 첫번째 카테고리"),
 *      @OA\Property(property="second_category", type="string", example="", description="컴포넌트 유형 두번째 카테고리<br/>첫번째 카테고리가 본사 유형 일 경우 필수"),
 *      @OA\Property(property="use_blank", type="boolean", example=true, description="여백 옵션 사용여부"),
 *      @OA\Property(property="use_all_page", type="boolean", example=true, description="전체 페이지 사용 (true일 경우 전체사용, false 일 경우 선택사용)"),
 *      @OA\Property(property="icon", type="string", example="header", description="컴포넌트 아이콘<br/>(header, footer, category, image, product, text, plugin, solution)"),
 *      @OA\Property(property="display", type="boolean", example=true, description="노출 여부"),
 *      @OA\Property(property="status", type="string", example="registering", description="상태 구분 (등록중, 등록완료)"),
 *      @OA\Property(property="manager_memo", type="string", example="registering", description="관리자 메모"),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="등록 일자"),
 *      @OA\Property(property="updatedAt", type="string", format="date-time", description="수정 일자")
 * )
 *
 *
 *  @OA\Schema(
 *      schema="ComponentModifyPossible",
 *      @OA\Property(property="solution_id", type="integer", example=1, description="솔루션 고유 번호"),
 *      @OA\Property(property="name", type="string", example="컴포넌트", description="컴포넌트 명"),
 *      @OA\Property(property="first_category", type="string", example="design", description="컴포넌트 유형 첫번째 카테고리"),
 *      @OA\Property(property="second_category", type="string", example="", description="컴포넌트 유형 두번째 카테고리<br/>첫번째 카테고리가 본사 유형 일 경우 필수"),
 *      @OA\Property(property="use_blank", type="boolean", example=true, description="여백 옵션 사용여부"),
 *      @OA\Property(property="use_all_page", type="boolean", example=true, description="전체 페이지 사용 (true일 경우 전체사용, false 일 경우 선택사용)"),
 *      @OA\Property(property="icon", type="string", example="header", description="컴포넌트 아이콘<br/>(header, footer, category, image, product, text, plugin, solution)"),
 *      @OA\Property(property="display", type="boolean", example=true, description="노출 여부"),
 *      @OA\Property(property="status", type="string", example="registering", description="상태 구분 (등록중, 등록완료)"),
 *      @OA\Property(property="manager_memo", type="string", example="registering", description="관리자 메모"),
 *  )
 *
 */
class Component extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;


    protected $fillable = [
        'user_partner_id',
        'solution_id',
        'name',
        'use_other_than_qpick',
        'use_other_than_maker',
        'first_category',
        'second_category',
        'use_blank',
        'use_all_page',
        'icon',
        'display',
        'status',
        'manager_memo',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public static array $onlyQpickCategory = [
        'product', 'design', 'solution'
    ];

    public static array $firstCategory = [
        'theme_component' => '테마 컴포넌트',
        'product' => '상품',
        'design' => '디자인',
        'solution' => '솔루션',
    ];

    public static array $secondCategory = [
        'basic' => '베이직',
        'dynamic' => '다이나믹',
    ];

    public static array $status = [
        'registering' => '등록중',
        'registered' => '등록완료'
    ];

    // 컴포넌트 아이콘
    public static array $icon = [
        'header' => '헤더',
        'footer' => '푸터',
        'category' => '카테고리',
        'image' => '이미지',
        'product' => '상품',
        'text' => '텍스트',
        'plugin' => '플러그인',
        'solution' => '솔루션'
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(UserPartner::class, 'user_partner_id');
    }

    public function solution(): BelongsTo
    {
        return $this->belongsTo(Solution::class, 'solution_id');
    }

    public function usableVersion(): HasMany
    {
        return $this->version()->where('usable', true);
    }

    public function version(): HasMany
    {
        return $this->hasMany(ComponentVersion::class);
    }

}

