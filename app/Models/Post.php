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

    public function reply()
    {
        return $this->hasMany('App\Models\Reply');
    }

    public function attachFiles()
    {
        return $this->morphMany('App\Models\AttachFile', 'attachable');
    }

    public function thumbnail()
    {
        return $this->hasOne('App\Models\PostThumbnail');
    }

    public function board()
    {
        return $this->belongsTo('App\Models\Board', 'board_id', 'id');
    }

    public function getAttachFileLimit(): int
    {
        return intval($this->board->options['attachLimit']);
    }

    public function checkAttachableModel(): bool
    {
        return intval($this->board->options['attach']) ? true : false;
    }


}
