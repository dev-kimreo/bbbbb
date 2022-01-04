<?php

namespace App\Models;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exception extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    public static string $exceptionEntity = "exception";

    protected $fillable = ['code', 'title'];
    protected $with = ['translation'];

    public function translation(): MorphOne
    {
        return $this->morphOne(Translation::class, 'linkable');
    }
}
