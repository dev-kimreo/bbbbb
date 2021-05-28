<?php

namespace App\Models;

use App\Models\Traits\DateFormatISO8601;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SignedCode extends Model
{
    use HasFactory, DateFormatISO8601;

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
}
