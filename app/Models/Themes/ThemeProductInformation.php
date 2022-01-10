<?php

namespace App\Models\Themes;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 * @OA\Schema(
 *      @OA\Property(property="id", type="integer", example=1, description="고유번호"),
 *      @OA\Property(property="theme_product_id", type="integer", example=1, description="테마 상품 고유 번호"),
 *      @OA\Property(property="description", type="text", example="테마 상품 상세설명입니다.", description="테마 상품 전시정보 상세설명"),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="등록 일자"),
 *      @OA\Property(property="updatedAt", type="string", format="date-time", description="수정 일자")
 * )
 *
 */
class ThemeProductInformation extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;


    public static string $exceptionEntity = "themeProductInformation";

    protected $fillable = [
        'theme_product_id', 'description'
    ];

    // 비즈니즈 타입
    public static array $businesses = [
        '패션/의류', '유아/아동', '화장품/잡화', '가구/인테리어/조명', '식품'
    ];

    // 레이아웃
    public static array $layouts = [
        'top-menu' => '상단 메뉴형', 'left-menu' => '좌측 메뉴형', 'top-image' => '상단 이미지형'
    ];

    // 판매 상품 갯수
    public static array $numberOfItemsSold = [
        'lessThan10' => '10개 미만', 'lessThan30' => '30개 미만', 'moreThan30' => '30개 이상'
    ];

    // 컬러타입
    public static array $colors = [
        'red' => '레드', 'yellow' => '옐로우/오렌지', 'green' => '그린', 'blue' => '블루', 'pink' => '핑크/바이올렛', 'brown' => '브라운', 'black' => '블랙', 'white'=> '화이트/그레이'
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];



}

