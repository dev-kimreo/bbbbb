<?php

namespace App\Models;

use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class UserLinkedSolution extends Model
{
    use HasFactory, DateFormatISO8601;

    protected $fillable = ['user_id', 'name', 'apikey'];

    public function user(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
