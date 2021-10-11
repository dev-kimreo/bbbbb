<?php

namespace App\Models\Widgets;

use App\Models\Traits\DateFormatISO8601;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *  @OA\Schema(
 *      schema="WidgetUsage",
 *      @OA\Property(property="id", type="integer", example=371, description="위젯 사용내역 고유번호" ),
 *      @OA\Property(property="widgetId", type="integer", example=14, description="위젯 고유번호" ),
 *      @OA\Property(property="sort", type="integer", example=2, description="위젯 표시순서" ),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="등록일자", readOnly="true"),
 *      @OA\Property(property="widget", type="array", @OA\Items(ref="#/components/schemas/Widget")),
 *  )
 *
 * @method static where(string $string, int|string|null $id)
 * @method static findOrFail(int $id)
 * @method static orderByDesc(string $string)
 * @method static create(array $array)
 * @method static orderBy(string $string)
 * @method static inRandomOrder()
 */
class WidgetUsage extends Model
{
    use HasFactory, DateFormatISO8601;

    public $timestamps = false;
    protected $fillable = ['user_id', 'widget_id', 'sort'];
    protected $hidden = ['user_id'];
    protected $casts = [
        'created_at' => 'datetime'
    ];
    protected $appends = [];
    protected $with = ['widget'];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->setAttribute('created_at', $model->freshTimestamp());
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->simplify('user');
    }

    public function widget(): BelongsTo
    {
        return $this->belongsTo(Widget::class);
    }
}
