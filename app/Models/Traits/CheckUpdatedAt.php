<?php

namespace App\Models\Traits;

trait CheckUpdatedAt
{
    public function getUpdatedAtAttribute($value)
    {
        if ($this->attributes['updated_at'] == $this->attributes['created_at']) {
            $this->attributes['updated_at'] = null;
        }
    }
}