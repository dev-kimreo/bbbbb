<?php

namespace App\Models;

use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Sofa\Eloquence\Eloquence;
use Sofa\Eloquence\Mappable;
use Carbon\Carbon;
use Str;



class Test extends Model
{
    use HasFactory, Eloquence, Mappable, DateFormatISO8601;


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

    protected $casts = [
    ];

//    public function getAttributes()
//    {
////        $aaa = [];
////        foreach ($this->attributes as $key => $val) {
////            $aaa[Str::camel($key)] = $val;
////        }
////
//////        return $aaa;
////
////        print_r($this->attributes);
//        foreach ($this->attributes as $key => $val ) {
//            $this->attributes[Str::camel($key)] = $val;
//        }
//
//        return $this->attributes;
//
//
////        if (array_key_exists($key, $this->relations)
////            || method_exists($this, $key)
////        )
////        {
////            return parent::getAttribute($key);
////        }
////        else
////        {
////            return parent::getAttribute(Str::snake($key));
////        }
//    }

    public function getAttribute($key) {
        if (array_key_exists($key, $this->relations)) {
            return parent::getAttribute($key);
        } else {
            return parent::getAttribute(Str::snake($key));
        }
    }

    public function setAttribute($key, $value)
    {
        return parent::setAttribute(Str::snake($key), $value);
    }
}
