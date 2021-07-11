<?php

namespace App\Models\Traits;

use Illuminate\Support\Carbon;

trait CheckUpdatedAt
{
    public function getUpdatedAtAttribute($value): ?string
    {
        $res = null;

        if ($this->attributes['updated_at'] != $this->attributes['created_at']) {
            $res = Carbon::parse($this->attributes['updated_at'])->format('c');
        }

        return $res;
    }
}