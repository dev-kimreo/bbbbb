<?php

namespace App\Models\Users;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

abstract class UserPrivacy extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $hidden = ['id', 'user_id'];
    protected $fillable = ['user_id', 'name', 'email'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
