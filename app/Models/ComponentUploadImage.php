<?php

namespace App\Models;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class ComponentUploadImage extends Model
{
    use HasFactory;
    use DateFormatISO8601;
    use CheckUpdatedAt;

    protected $fillable = ['attach_file_id', 'url_thumb', 'width', 'height'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts = [];
    protected $appends = [];
    protected $with = ['attachFile'];

    public function attachFile(): MorphOne
    {
        return $this->morphOne(AttachFile::class, 'attachable', 'attachable_type', 'attachable_id');
    }
}
