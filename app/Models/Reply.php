<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;
use Carbon\Carbon;



class Reply extends Model
{
    use HasFactory, Eloquence, Mappable;

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
        'post_id',
        'user_id',
        'created_at',
        'updated_at'
    ];

    protected $maps = [
        'postId' => 'post_id',
        'userId' => 'user_id',
        'regDate' => 'created_at',
        'uptDate' => 'updated_at',
    ];

    protected $appends = [
        'userId',
        'regDate',
        'uptDate'
    ];

    protected $casts = [
    ];

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id')->select(['id', 'name']);
    }

    public function getCreatedAtAttribute($value){
        return Carbon::parse($value)->format('c');
    }

    public function getUpdatedAtAttribute($value){
        return Carbon::parse($value)->format('c');
    }
}
