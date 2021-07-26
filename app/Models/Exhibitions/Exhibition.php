<?php

namespace App\Models\Exhibitions;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exhibition extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    protected $fillable = [
        'exhibition_category_id', 'started_at', 'ended_at', 'target_opt', 'target_grade',
        'sort', 'visible'
    ];
    protected $hidden = [
        'id', 'exhibition_category_id', 'exhibitable_type', 'exhibitable_id', 'target_opt', 'target_grade',
        'created_at', 'updated_at', 'deleted_at',
    ];
    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'target_grade' => 'array',
        'visible' => 'boolean'
    ];
    protected $appends = ['target'];
    protected $with = ['category'];

    // 타겟 설정
    public static array $targetOpt = ['all', 'grade', 'designate'];
    public static array $targetGrade = ['associate', 'regular'];

    public function exhibitable(): MorphTo
    {
        return $this->morphTo();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExhibitionCategory::class, 'exhibition_category_id')->simplify();
    }

    public function targetUsers(): HasMany
    {
        return $this->hasMany(ExhibitionTargetUser::class);
    }

    public function getTargetAttribute(): array
    {
        return [
            'opt' => $this->getAttribute('target_opt'),
            'grade' => $this->getAttribute('target_grade'),
            'users' => $this->targetUsers()->get()->pluck('user_id')
        ];
    }

    public function delete(): ?bool
    {
        $this->targetUsers()->delete();
        return parent::delete();
    }

    public function getParentRelation(): Relation
    {
        return $this->exhibitable();
    }
}
