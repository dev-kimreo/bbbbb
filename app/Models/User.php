<?php

namespace App\Models;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Auth;
use DB;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Laravel\Passport\HasApiTokens;

/**
 * @OA\Schema(
 *   schema="User",
 *   required={"password"},
 *   @OA\Property(property="id", type="integer", readOnly="true", example="1"),
 *   @OA\Property(property="name", type="string", maxLength=255, example="홍길동"),
 *   @OA\Property(property="email", type="string", readOnly="true", format="email", description="회원 email", example="user@qpicki.com"),
 *   @OA\Property(
 *       property="emailVerifiedAt", type="string", readOnly="true", format="date-time",
 *       description="회원 이메일 인증 일자", default=null, example="2019-02-25 12:59:20"
 *   ),
 *   @OA\Property(property="grade", type="integer", default=1, description="0:준회원, 1:정회원", example=1),
 *   @OA\Property(property="lanugage", type="string", default="ko", description="선택한 언어코드", example="ko"),
 *   @OA\Property(
 *       property="registerdAt", type="string", readOnly="true", format="date-time",
 *       description="정회원 등록일", default=null, example="2019-02-25 12:59:20"
 *   ),
 *   @OA\Property(
 *       property="inactivatedAt", type="string", readOnly="true", format="date-time",
 *       description="(휴면계정인 경우) 휴면일", default=null, example="2019-02-25 12:59:20"
 *   ),
 *   @OA\Property(
 *       property="lastAuthorizedAt", type="string", readOnly="true", format="date-time",
 *       description="최종로그인 일시", default=null, example="2019-02-25 12:59:20"
 *   ),
 *   @OA\Property(property="createdAt", ref="#/components/schemas/Base/properties/created_at"),
 *   @OA\Property(property="updatedAt", ref="#/components/schemas/Base/properties/updated_at"),
 *   @OA\Property(property="advAgree", ref="#/components/schemas/UserAdvAgree"),
 *   @OA\Property(property="sites", type="array", @OA\Items(ref="#/components/schemas/UserSite"))
 * )
 *
 * @OA\Schema (
 *   schema="UserSimply",
 *   @OA\Property(property="id", type="integer", readOnly="true", example="1"),
 *   @OA\Property(property="name", type="string", maxLength=255, example="홍길동"),
 *   @OA\Property(property="email", type="string", readOnly="true", format="email", description="회원 email", example="user@qpicki.com"),
 * )
 *
 * Class User
 * @package App\Models
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    // 회원 등급
    public array $userGrade = [
        0,  // 준회원
        1   // 정회원
    ];

    protected $fillable = [
        'name', 'email', 'language', 'password', 'memo_for_managers'
    ];
    protected $hidden = ['password', 'remember_token', 'deleted_at', 'memo_for_managers'];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'registered_at' => 'datetime',
        'inactivated_at' => 'datetime',
        'last_authorized_at' => 'datetime',
    ];

    public function advAgree(): HasOne
    {
        return $this->hasOne(UserAdvAgree::class);
    }

    public function sites(): hasMany
    {
        return $this->hasMany(UserSite::class);
    }

    public function checkAdmin(): bool
    {
        return $this->manager()->exists();
    }

    public function manager(): HasOne
    {
        return $this->hasOne(Manager::class);
    }

    public function authority(): BelongsToMany
    {
        return $this->belongsToMany('App\Models\Authority', 'managers')->wherePivot('deleted_at', null);
    }

    public function inquiry(): HasMany
    {
        return $this->hasMany(Inquiry::class);
    }

    public function referredInquiry(): HasMany
    {
        return $this->hasMany(Inquiry::class, 'referrer_id', 'id');
    }

    public function assignedInquiry(): HasMany
    {
        return $this->hasMany(Inquiry::class, 'assignee_id', 'id');
    }

    public function scopeSimplify($query, $type)
    {
        if ($type == 'manager') {
            // 관리자 권한을 가진 회원은 이름을 관리그룹 닉네임으로 바꾸어 출력
            $res = $query
                ->leftJoin('managers', 'users.id', '=', 'managers.user_id')
                ->leftJoin('authorities', 'managers.authority_id', '=', 'authorities.id')
                ->select(['users.id', DB::raw('IFNULL(authorities.display_name, users.name) as name'), 'users.email']);
        } else {
            // 회원정보에 기재된 본래의 이름을 그대로 출력
            $res = $query->select(['id', 'name', 'email']);
        }

        return $res;
    }

    public function backofficeLogs(): MorphMany
    {
        return $this->morphMany(BackofficeLog::class, 'loggable');
    }
}

