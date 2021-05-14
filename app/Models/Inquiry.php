<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Inquiry extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = false;

    public $status = [
        'wating',       // 접수
        'answering',    // 확인중
        'answered',     // 완료
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at'
    ];

    protected $appends = [
    ];

    protected $casts = [
    ];

    // 파일 첨부 갯수 제한
    public function getAttachFileLimit() {
        return 10;
    }

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->select(['id', 'name']);
    }

    public function answer()
    {
        return $this->hasOne('App\Models\InquiryAnswer');
    }

    public function attachFiles()
    {
        return $this->morphMany('App\Models\AttachFile', 'attachable');
    }

    public function getCreatedAtAttribute($value){
        return Carbon::parse($value)->format('c');
    }

    public function getUpdatedAtAttribute($value){
        return $value ? Carbon::parse($value)->format('c') : $value;
    }
}
