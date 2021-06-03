<?php

namespace App\Models;

use App\Models\Traits\CheckUpdatedAt;
use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;


class PostThumbnail extends Model
{
    use HasFactory, SoftDeletes, DateFormatISO8601, CheckUpdatedAt;

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
    ];

    protected $appends = [
    ];

    protected $casts = [
    ];

    public function post()
    {
        return $this->belongsTo('App\Models\Post');
    }

    // 파일 첨부 갯수 제한
    public function getAttachFileLimit() {
        return 1;
    }

    public function attachFiles()
    {
        return $this->morphOne('App\Models\AttachFile', 'attachable');
    }
}
