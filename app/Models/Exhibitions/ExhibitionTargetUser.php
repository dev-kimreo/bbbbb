<?php

namespace App\Models\Exhibitions;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExhibitionTargetUser extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = false;
    protected $fillable = ['exhibition_id', 'user_id'];
    protected $hidden = ['deleted_at'];
    protected $casts = [];
    protected $appends = [];

    public function exhibition(): BelongsToMany
    {
        return $this->belongsToMany(Exhibition::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
