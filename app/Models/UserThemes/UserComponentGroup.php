<?php

namespace App\Models\UserThemes;

use App\Models\Themes\Theme;
use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *
 *
 */
class UserComponentGroup extends Model
{
    use HasFactory;
    use SoftDeletes;
    use DateFormatISO8601;

    public static string $exceptionEntity = "userComponentGroup";
    protected $fillable = [];
    protected $hidden = [];


}
