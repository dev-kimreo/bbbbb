<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SignedCode extends Model
{
    use HasFactory;

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public static function scopeNameId($q, $nameId)
    {
        return $q->where('user_id', $nameId);
    }

    public static function getBySignCode($id, $hash, $sign)
    {
        return self::nameId($id)
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
