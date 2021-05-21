<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class InquiryAnswer extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = false;

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
        'deleted_at'
    ];

    protected $appends = [
    ];

    protected $casts = [
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->select(['id', 'name']);
    }

    public function inquiry(): belongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function getCreatedAtAttribute($value): string
    {
        return Carbon::parse($value)->format('c');
    }

    public function getUpdatedAtAttribute($value): ?string
    {
        return $value ? Carbon::parse($value)->format('c') : $value;
    }
}
