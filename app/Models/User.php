<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

use Laravel\Passport\HasApiTokens;

/**
 *
 * @OA\Schema(
 * required={"password"},
 * @OA\Property(property="id", type="integer", readOnly="true", example="1"),
 * @OA\Property(property="email", type="string", readOnly="true", format="email", description="회원 email", example="user@qpicki.com"),
 * @OA\Property(
 *     property="emailVerifiedAt", type="string", readOnly="true", format="date-time",
 *     description="회원 이메일 인증 일자", default=null, example="2019-02-25 12:59:20"
 * ),
 * @OA\Property(property="name", type="string", maxLength=255, example="홍길동"),
 * @OA\Property(property="grade", type="integer", default=1, description="0:준회원, 1:정회원", example=1),
 * @OA\Property(property="createdAt", ref="#/components/schemas/Base/properties/created_at"),
 * @OA\Property(property="updatedAt", ref="#/components/schemas/Base/properties/updated_at"),
 * )
 *
 * Class User
 *
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens;

    // 회원 등급
    public $userGrade = [
        0,  // 준회원
        1   // 정회원
    ];

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
        'remember_token'
    ];

    public function checkAdmin(): bool
    {
        return $this->manager()->exists();
    }

    public function manager(): HasOne
    {
        return $this->hasOne(Manager::class);
    }

    public function inquiry(): HasMany
    {
        return $this->hasMany(Inquiry::class, 'user_id', 'id');
    }

    public function assignedInquiry(): HasMany
    {
        return $this->hasMany(Inquiry::class, 'assignee_id', 'id');
    }

    public function isLoginToManagerService(): bool
    {
        return $this->token()->client_id == 2 ? true : false;
    }

    public function checkUsableManagerService(): bool
    {
        return $this->manager && $this->isLoginToManagerService();
    }

    public function getEmailVerifiedAtAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('c') : $value;
    }

    public function getCreatedAtAttribute($value): string
    {
        return Carbon::parse($value)->format('c');
    }

    public function getUpdatedAtAttribute($value): string
    {
        return Carbon::parse($value)->format('c');
    }

}

