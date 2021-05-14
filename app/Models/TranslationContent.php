<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TranslationContent extends Model
{
    use HasFactory, SoftDeletes;

    public function word()
    {
        return $this->belongsTo(Translation::class);
    }
}
