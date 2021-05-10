<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

use Laravel\Passport\HasApiTokens;

/**
 *
 * @OA\Schema(
 * required={"password"},
 * @OA\Xml(name="User1"),
 * @OA\Property(property="id", type="integer", readOnly="true", example="1"),
 * @OA\Property(property="role", type="string", readOnly="true", description="User role"),
 * @OA\Property(property="email", type="string", readOnly="true", format="email", description="User unique email address", example="user@gmail.com"),
 * @OA\Property(property="email_verified_at", type="string", readOnly="true", format="date-time", description="Datetime marker of verification status", example="2019-02-25 12:59:20"),
 * @OA\Property(property="first_name", type="string", maxLength=32, example="John"),
 * @OA\Property(property="last_name", type="string", maxLength=32, example="Doe"),
 * @OA\Property(property="created_at", ref="#/components/schemas/Base/properties/created_at"),
 * @OA\Property(property="updated_at", ref="#/components/schemas/Base/properties/updated_at"),
 * @OA\Property(property="deleted_at", ref="#/components/schemas/Base/properties/deleted_at")
 * )
 *
 * Class User
 *
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens;

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
        'password'
    ];

    public function checkAdmin()
    {
        return $this->grade != 100 ? false : true;
    }

    public function isLoginToManagerService()
    {
        return $this->token()->client_id == 2 ? true : false;
    }

    public function getEmailVerifiedAtAttribute($value)
    {
        if (isset($value)) {
            return Carbon::parse($value)->format('c');
        }
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('c');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('c');
    }

}

