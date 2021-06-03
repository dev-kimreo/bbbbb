<?php

namespace App\Models;

use App\Models\Traits\DateFormatISO8601;
use App\Models\Traits\CheckUpdatedAt;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserLinkedSolution extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    protected $fillable = ['user_id', 'name', 'apikey'];
    protected $hidden = ['deleted_at'];

    public function user(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
