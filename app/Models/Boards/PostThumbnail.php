<?php

namespace App\Models\Boards;

use App\Models\Attach\AttachFile;
use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostThumbnail extends Model
{
    use HasFactory;
    use SoftDeletes;
    use DateFormatISO8601;
    use CheckUpdatedAt;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    protected $appends = [
    ];

    protected $casts = [
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    // 파일 첨부 갯수 제한
    public function getAttachFileLimit(): int
    {
        return 1;
    }

    public function attachFiles(): MorphOne
    {
        return $this->morphOne(AttachFile::class, 'attachable');
    }
}
