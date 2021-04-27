<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;
use Carbon\Carbon;



class Post extends Model
{
    use HasFactory, Eloquence, Mappable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'board_id',
        'user_id',
        'title',
        'content',
    ];


    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'board_id',
        'user_id',
        'created_at',
        'updated_at'
    ];

    protected $maps = [
        'boardId' => 'board_id',
        'userId' => 'user_id',
        'regDate' => 'created_at',
        'uptDate' => 'updated_at',
    ];

    protected $appends = [
        'boardId',
        'userId',
        'regDate',
        'uptDate'
    ];

    protected $casts = [
        'etc' => 'array'
    ];

    public function getCreatedAtAttribute($value){
        return Carbon::parse($value)->format('c');
    }

    public function getUpdatedAtAttribute($value){
        return Carbon::parse($value)->format('c');
    }

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->select(['id', 'name']);
    }

    public function replyCount(){
        return $this->hasMany('App\Models\Reply', 'post_id', 'id')->selectRaw('count(id) as count');
    }

    public function thumbnails(){
        return $this->hasOne('App\Models\AttachFile')->select(['url']);
    }

}
