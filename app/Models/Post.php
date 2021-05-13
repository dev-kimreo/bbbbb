<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;


class Post extends Model
{
    use HasFactory, SoftDeletes;

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
    ];

    protected $appends = [
    ];

    protected $casts = [
        'etc' => 'array'
    ];

    public function scopeId($q, $id)
    {
        return $q->where('id', $id);
    }

    public function scopeBoardId($q, $boardId)
    {
        return $q->where('board_id', $boardId);
    }

    public function getByBoardId($id, $boardId)
    {
        return $this->id($id)->boardId($boardId);
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('c');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('c');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->select(['id', 'name']);
    }

    public function replyCount()
    {
        return $this->hasMany('App\Models\Reply', 'post_id', 'id')->selectRaw('count(id) as count');
    }

    public function attachFiles()
    {
        return $this->morphMany('App\Models\AttachFile', 'attachable');
    }



}
