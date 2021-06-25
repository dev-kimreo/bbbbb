<?php

namespace App\Models\Exhibitions;

use App\Libraries\StringLibrary;
use App\Models\AttachFile;
use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 *  @OA\Schema(
 *      schema="ExhibitionBanner",
 *      @OA\Property(property="id", type="integer", example=1, description="고유번호(PK)"),
 *      @OA\Property(property="title", type="string", example="7월 광고배너", description="배너 제목"),
 *      @OA\Property(property="url", type="url", example="https://help.qpick.com/board/1", description="링크 URL"),
 *      @OA\Property(property="gaCode", type="url", example="27bf63c92ced856d1b931162a15383e3", description="구글 애널리틱스 트래킹 코드"),
 *      @OA\Property(property="memo", type="string", example="7월 회원가입 이벤트 광고배너", description="배너 설명"),
 *      @OA\Property(property="createdAt", type="string", format="date-time", description="등록일자"),
 *      @OA\Property(property="updatedAt", type="string", format="date-time", description="수정일자"),
 *      @OA\Property(property="exhibition", type="object",
 *          @OA\Property(property="startedAt", type="string", format="date-time", description="전시기간 시작일자"),
 *          @OA\Property(property="endedAt", type="string", format="date-time", description="전시기간 종료일자"),
 *          @OA\Property(property="sort", type="integer", example=999, description="전시기간 종료일자"),
 *          @OA\Property(property="visible", type="boolean", example=true, description="전시여부<br />true:노출<br />false:숨김"),
 *          @OA\Property(property="target", type="object",
 *              @OA\Property(property="opt", type="string", example="grade", description="전시 타겟설정<br />all:모든 회원<br />grade:회원구분<br />designate:특정회원"),
 *              @OA\Property(property="grade", type="array", description="선택된 회원등급, 타겟설정이 [회원구분]인 경우에 표시<br />associate:준회원<br />regular:정회원", @OA\Items(
 *                  type="string", example="associate"
 *              )),
 *              @OA\Property(property="users", type="array", description="선택된 회원의 고유번호(PK), 타겟설정이 [특정회원]인 경우에 표시", @OA\Items(
 *                  type="integer", example=217
 *              )),
 *          ),
 *          @OA\Property(property="category", type="object",
 *              @OA\Property(property="id", type="integer", example=74, description="전시관리 카테고리 고유번호"),
 *              @OA\Property(property="name", type="string", example="메인 중앙배너", description="전시관리 카테고리 이름"),
 *          )
 *      ),
 *      @OA\Property(property="contents", type="array", @OA\Items(
 *          @OA\Property(property="id", type="id", example="38", description="배너 콘텐츠 고유번호(PK)"),
 *          @OA\Property(property="device", type="string", example="mobile", description="디바이스명 (pc 또는 mobile)"),
 *          @OA\Property(property="attachFiles", type="object", description="첨부파일", ref="#/components/schemas/AttachFile")
 *      )),
 *      @OA\Property(property="creator", type="object", ref="#/components/schemas/UserSimply"),
 *  )
 *
 * Class Banner
 * @package App\Models\Exhibitions
 */
class Banner extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

    protected $fillable = ['user_id', 'title', 'url', 'ga_code', 'memo'];
    protected $hidden = ['deleted_at', 'user_id'];
    protected $casts = [];
    protected $appends = [];
    protected $with = ['exhibition', 'contents', 'creator'];

    public function exhibition(): MorphOne
    {
        return $this->morphOne(Exhibition::class, 'exhibitable');
    }

    public function targetUsers(): HasManyThrough
    {
        return $this->hasManyThrough(ExhibitionTargetUser::class, Exhibition::class, 'exhibitable_id')
            ->where('exhibitable_type', 'banner');
    }

    public function contents(): HasMany
    {
        return $this->hasMany(BannerDeviceContent::class);
    }

    public function contentsAttachFiles(): HasManyThrough
    {
        //return $this->hasManyThrough(AttachFile::class, BannerDeviceContent::class, 'banner_id', 'attachable_id');
        return $this->hasManyThrough(AttachFile::class, BannerDeviceContent::class, 'banner_id', 'attachable_id')
            ->where('attachable_type', 'banner_content');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->simplify('manager');
    }

    public function getDevicesAttribute() {
        return collect($this->getAttribute('contents'))->pluck('device');
    }

    public function scopeWhereHasCategory($q, $v)
    {
        return $q->whereHas('exhibition', function(Builder $q) use ($v) {
            $q->whereHas('category', function (Builder $q) use ($v) {
                $q->where('name', 'like', '%' . StringLibrary::escapeSql($v) . '%');
            });
        });
    }

    public function delete(): ?bool
    {
        $this->targetUsers()->delete();
        $this->exhibition()->delete();
        $this->contentsAttachFiles()->delete();
        $this->contents()->delete();
        return parent::delete();
    }
}
