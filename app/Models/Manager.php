<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;


class Manager extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'updated_at', 'deleted_at'
    ];

    protected $with = [
        'user', 'authority'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function authority()
    {
        return $this->belongsTo(Authority::class);
    }
}

