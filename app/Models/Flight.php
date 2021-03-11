<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;
use Carbon\Carbon;

class Flight extends Model
{
    use HasFactory, Eloquence, Mappable;

    protected $maps = [
        'no' => 'id',
        'name' => 'aaa_name',
        'regDate' => 'created_at',
        'uptDate' => 'updated_at'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'id',
        'aaa_name',
        'created_at',
        'updated_at',
    ];

    protected $appends = [
        'no',
        'name',
        'regDate',
        'uptDate',
    ];

    public function getCreatedAtAttribute($value){
        return Carbon::parse($value)->format('c');
    }

    public function getUpdatedAtAttribute($value){
        return Carbon::parse($value)->format('c');
    }
}
