<?php

namespace App\Models\Users;

use App\Exceptions\QpickHttpException;
use App\Models\ActionLog;
use App\Models\Inquiries\Inquiry;
use App\Models\Manager;
use App\Models\Themes\Theme;
use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use App\Models\UserThemes\UserThemePurchaseHistory;
use Carbon\Carbon;
use Closure;
use DB;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
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
 *   @OA\Property(property="memoForManagers", type="string", description="관리자 메모", example="어뷰징 기록이 있는 사용자입니다"),
 *   @OA\Property(
 *       property="registerdAt", type="datetime", readOnly="true", format="date-time",
 *       description="정회원 등록일", default=null, example="2021-06-05T09:00:00+00:00"
 *   ),
 *   @OA\Property(
 *       property="inactivatedAt", type="datetime", readOnly="true", format="date-time",
 *       description="(휴면계정인 경우) 휴면일", default=null, example="2021-06-05T09:00:00+00:00"
 *   ),
 *   @OA\Property(
 *       property="deletedAt", type="datetime", readOnly="true", format="date-time",
 *       description="(탈퇴계정인 경우) 탈퇴처리일", default=null, example="2021-06-05T09:00:00+00:00"
 *   ),
 *   @OA\Property(
 *       property="lastAuthorizedAt", type="datetime", readOnly="true", format="date-time",
 *       description="최종로그인 일시", default=null, example="2021-06-05T09:00:00+00:00"
 *   ),
 *   @OA\Property(property="createdAt", ref="#/components/schemas/Base/properties/created_at"),
 *   @OA\Property(property="updatedAt", ref="#/components/schemas/Base/properties/updated_at"),
 *   @OA\Property(property="advAgree", ref="#/components/schemas/UserAdvAgree"),
 *   @OA\Property(property="sites", type="array", @OA\Items(ref="#/components/schemas/UserSite"))
 * )
 *
 * @OA\Schema(
 *   schema="UserWithoutPrivacy",
 *   required={"password"},
 *   @OA\Property(property="id", type="integer", readOnly="true", example="1"),
 *   @OA\Property(
 *       property="emailVerifiedAt", type="string", readOnly="true", format="date-time",
 *       description="회원 이메일 인증 일자", default=null, example="2019-02-25 12:59:20"
 *   ),
 *   @OA\Property(property="grade", type="integer", default=1, description="0:준회원, 1:정회원", example=1),
 *   @OA\Property(property="lanugage", type="string", default="ko", description="선택한 언어코드", example="ko"),
 *   @OA\Property(property="memoForManagers", type="string", description="관리자 메모", example="어뷰징 기록이 있는 사용자입니다"),
 *   @OA\Property(
 *       property="registerdAt", type="datetime", readOnly="true", format="date-time",
 *       description="정회원 등록일", default=null, example="2021-06-05T09:00:00+00:00"
 *   ),
 *   @OA\Property(
 *       property="inactivatedAt", type="datetime", readOnly="true", format="date-time",
 *       description="(휴면계정인 경우) 휴면일", default=null, example="2021-06-05T09:00:00+00:00"
 *   ),
 *   @OA\Property(
 *       property="deletedAt", type="datetime", readOnly="true", format="date-time",
 *       description="(탈퇴계정인 경우) 탈퇴처리일", default=null, example="2021-06-05T09:00:00+00:00"
 *   ),
 *   @OA\Property(
 *       property="lastAuthorizedAt", type="datetime", readOnly="true", format="date-time",
 *       description="최종로그인 일시", default=null, example="2021-06-05T09:00:00+00:00"
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
 * @method static create(array|string[] $array_merge)
 * @method static find(int $id)
 * @method static findOrFail(int $id)
 * @method static select(string[] $array)
 * @method static selectRaw(string $string)
 * @method static status(string $string)
 * @method static updateOrCreate(array $array, array $array1)
 * @method static where(string $string, string $string1, Carbon $addDays)
 * @method static whereHas(string $string, Closure $param)
 * @method static inRandomOrder()
 * @property mixed privacy
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    public static string $exceptionEntity = "user";

    // 회원 등급
    public static array $userGrade = [
        0 => 'associate',  // 준회원
        1 => 'regular'  // 정회원
    ];

    protected $fillable = [
        'grade', 'language', 'password', 'memo_for_managers'
    ];
    protected $hidden = ['password', 'remember_token', 'memo_for_managers'];
    protected $casts = [
        'email_verified_at' => 'datetime',
        'registered_at' => 'datetime',
        'inactivated_at' => 'datetime',
        'last_authorized_at' => 'datetime',
        'last_password_changed_at' => 'datetime',
    ];
    protected static string $statusMode = 'active';

    public function advAgree(): HasOne
    {
        return $this->hasOne(UserAdvAgree::class);
    }

    public function sites(): hasMany
    {
        return $this->hasMany(UserSite::class);
    }

    public function solutions(): hasMany
    {
        return $this->hasMany(UserSolution::class);
    }

    public function checkAdmin(): bool
    {
        return $this->manager()->exists();
    }

    public function manager(): HasOne
    {
        return $this->hasOne(Manager::class);
    }

    public function partner(): HasOne
    {
        return $this->hasOne(UserPartner::class);
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

    public function purchasingThemes(): BelongsToMany
    {
        return $this->belongsToMany(Theme::class, UserThemePurchaseHistory::query()->getModel()->getTable());
    }

    public function privacy(): HasOne
    {
        if (self::$statusMode == 'active') {
            $model = UserPrivacyActive::class;
        } elseif (self::$statusMode == 'inactive') {
            $model = UserPrivacyInactive::class;
        } else {
            $model = UserPrivacyDeleted::class;
        }

        return $this->hasOne($model);
    }

    public function scopeSimplify($query, $type)
    {
        if ($type == 'manager') {
            // 관리자 권한을 가진 회원은 이름을 관리그룹 닉네임으로 바꾸어 출력
            $res = $query
                ->leftJoin('user_privacy_active', 'users.id', '=', 'user_privacy_active.id')
                ->leftJoin('managers', 'users.id', '=', 'managers.user_id')
                ->leftJoin('authorities', 'managers.authority_id', '=', 'authorities.id')
                ->select(['users.id', 'users.grade as grade', DB::raw('IFNULL(authorities.display_name, user_privacy_active.name) as name'), 'user_privacy_active.email']);
        } else {
            // 회원정보에 기재된 본래의 이름을 그대로 출력
            $res = $query
                ->leftJoin('user_privacy_active', 'users.id', '=', 'user_privacy_active.id')
                ->select(['users.id', 'users.grade as grade', 'user_privacy_active.name as name', 'user_privacy_active.email as email']);
        }

        return $res;
    }

    /**
     * @throws QpickHttpException
     */
    public static function scopeStatus($query, $status)
    {
        self::$statusMode = $status;

        if ($status == 'active') {
            $query->whereNull('inactivated_at');
        } elseif ($status == 'inactive') {
            $query->whereNotNull('inactivated_at');
        } elseif ($status == 'deleted') {
            $query->onlyTrashed();
            $query->whereNotNull('deleted_at');
        } else {
            throw new QpickHttpException(422, 'common.bad_request', 'status');
        }
    }

    public function backofficeLogs(): MorphMany
    {
        return $this->morphMany(ActionLog::class, 'loggable')->forBackoffice();
    }

    public function actionLogs(): MorphMany
    {
        return $this->morphMany(ActionLog::class, 'loggable');
    }

    public function findForPassport($username)
    {
        return $this->status('active')->whereHas('privacy', function (Builder $q) use ($username) {
            $q->where('email', $username);
        })->first();
    }

    public function getEmailForPasswordReset(): string
    {
        return $this->privacy->email;
    }
}

