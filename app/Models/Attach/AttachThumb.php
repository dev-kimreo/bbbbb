<?php

namespace App\Models\Attach;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AttachThumb extends AttachFile
{
    use HasFactory;

    protected $table = 'attach_files';
    protected $hidden = ['id', 'attachable_type', 'attachable_id', 'user_id', 'created_at', 'updated_at', 'deleted_at'];
    protected $with = [];

    public function attachable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'attachable_type', 'attachable_id');
    }
}
