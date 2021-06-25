<?php

namespace App\Models\Exhibitions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PopupDeviceContent extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = false;
    protected $fillable = ['popup_id', 'device', 'contents'];
    protected $hidden = ['popup_id', 'deleted_at'];
    protected $casts = [];
    protected $appends = [];

    public function popup(): BelongsTo
    {
        return $this->belongsTo(Popup::class);
    }
}
