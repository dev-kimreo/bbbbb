<?php

namespace App\Models\Attach;

use App\Models\Traits\DateFormatISO8601;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * @method static findOrFail(int $id)
 */
class ComponentUploadImage extends Model
{
    use HasFactory;
    use DateFormatISO8601;

    public $timestamps = false;
    protected $fillable = ['attach_file_id', 'user_id', 'width', 'height'];
    protected $hidden = ['created_at', 'deleted_at'];
    protected $casts = [];
    protected $appends = [];
    protected $with = ['attachFile'];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = $model->created_at ?? $model->freshTimestamp();
        });

        static::deleting(function ($model) {
            $model->attachFile->delete();
        });
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function attachFile(): MorphOne
    {
        return $this->morphOne(AttachFile::class, 'attachable', 'attachable_type', 'attachable_id');
    }
}
