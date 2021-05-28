<?php

namespace App\Models;

use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TranslationContent extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601;

    public function word()
    {
        return $this->belongsTo(Translation::class);
    }
}
