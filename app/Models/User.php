<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;
use Carbon\Carbon;

use Laravel\Passport\HasApiTokens;


class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, Eloquence, Mappable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'id',
        'email_verified_at',
        'created_at',
        'updated_at'
    ];

    protected $maps = [
        'no' => 'id',
        'emailVerifiedDate' => 'email_verified_at',
        'regDate' => 'created_at',
        'uptDate' => 'updated_at'
    ];

    protected $appends = [
        'no',
        'emailVerifiedDate',
        'regDate',
        'uptDate',
    ];



    public function getEmailVerifiedAtAttribute($value){
        if (isset($value)) {
            return Carbon::parse($value)->format('c');
        }
    }

    public function getCreatedAtAttribute($value){
        return Carbon::parse($value)->format('c');
    }

    public function getUpdatedAtAttribute($value){
        return Carbon::parse($value)->format('c');
    }

}
