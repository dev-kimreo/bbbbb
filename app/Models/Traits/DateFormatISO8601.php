<?php

namespace App\Models\Traits;

use DateTimeInterface;

trait DateFormatISO8601
{
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('c');
    }
}