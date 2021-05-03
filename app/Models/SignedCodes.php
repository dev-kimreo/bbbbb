<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SignedCodes extends Model
{
    use HasFactory;

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public static function scopeNameId($q, $nameId)
    {
        return $q->where('name_id', $nameId);
    }

    public static function scopeName($q, $name)
    {
        return $q->where('name', $name);
    }

    public static function getBySignCode($exp, $id, $hash, $sign)
    {
        return self::name($exp[count($exp) - 2])
            ->nameId($id)
            ->where('hash', $hash)
            ->where('sign', $sign);
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('c');
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format('c');
    }
}
