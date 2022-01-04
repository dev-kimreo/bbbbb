<?php

namespace App\Models\Components;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 * @OA\Schema
 *      @OA\Property(property="id", type="integer", example=1, description="고유번호"),
 *      @OA\Property(property="component_type_id", type="integer", example=1, description="컴포넌트 옵션 유형 고유번호"),
 *      @OA\Property(property="type", type="string", example="text", description="유형 type<br/>text, integer, boolean, file, alt, url"),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="등록 일자"),
 *      @OA\Property(property="updatedAt", type="string", format="date-time", description="수정 일자")
 * )
 *
 */
class ComponentTypeProperty extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    public static string $exceptionEntity = "componentTypeProperty";

    protected $fillable = [
        'component_type_id', 'type'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public static array $types = ['boolean', 'integer', 'file', 'alt', 'url', 'text'];


}

