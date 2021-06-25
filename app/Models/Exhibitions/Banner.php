<?php

namespace App\Models\Exhibitions;

use App\Libraries\StringLibrary;
use App\Models\AttachFile;
use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    protected $fillable = ['user_id', 'title', 'url', 'ga_code', 'memo'];
    protected $hidden = ['deleted_at'];
    protected $casts = [];
    protected $appends = [];
    protected $with = ['exhibition', 'contents', 'creator'];

    public function exhibition(): MorphOne
    {
        return $this->morphOne(Exhibition::class, 'exhibitable');
    }

    public function targetUsers(): HasManyThrough
    {
        return $this->hasManyThrough(ExhibitionTargetUser::class, Exhibition::class, 'exhibitable_id')
            ->where('exhibitable_type', 'banner');
    }

    public function contents(): HasMany
    {
        return $this->hasMany(BannerDeviceContent::class);
    }

    public function contentsAttachFiles(): HasManyThrough
    {
        //return $this->hasManyThrough(AttachFile::class, BannerDeviceContent::class, 'banner_id', 'attachable_id');
        return $this->hasManyThrough(AttachFile::class, BannerDeviceContent::class, 'banner_id', 'attachable_id')
            ->where('attachable_type', 'banner_content');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->simplify('manager');
    }

    public function getDevicesAttribute() {
        return collect($this->getAttribute('contents'))->pluck('device');
    }

    public function scopeWhereHasCategory($q, $v)
    {
        return $q->whereHas('exhibition', function(Builder $q) use ($v) {
            $q->whereHas('category', function (Builder $q) use ($v) {
                $q->where('name', 'like', '%' . StringLibrary::escapeSql($v) . '%');
            });
        });
    }

    public function delete(): ?bool
    {
        $this->targetUsers()->delete();
        $this->exhibition()->delete();
        $this->contentsAttachFiles()->delete();
        $this->contents()->delete();
        return parent::delete();
    }
}
