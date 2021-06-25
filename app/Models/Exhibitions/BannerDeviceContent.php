<?php

namespace App\Models\Exhibitions;

use App\Models\AttachFile;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class BannerDeviceContent extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = false;
    protected $fillable = ['banner_id', 'device'];
    protected $hidden = ['banner_id', 'deleted_at'];
    protected $casts = [];
    protected $appends = [];
    protected $with = ['attachFile'];

    public function banner(): BelongsTo
    {
        return $this->belongsTo(Banner::class);
    }

    public function attachFile(): MorphOne
    {
        return $this->morphOne(AttachFile::class, 'attachable', 'attachable_type', 'attachable_id');
    }

    public function getAttachFileLimit(): int
    {
        return 1;
    }

    public function delete(): ?bool
    {
        $this->attachFile()->delete();
        return parent::delete();
    }
}
