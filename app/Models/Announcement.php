<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'popup_style',
        'is_active',
        'starts_at',
        'ends_at',
        'created_by',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'starts_at'  => 'datetime',
        'ends_at'    => 'datetime',
    ];

    /**
     * Relasi ke user yang membuat.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope: hanya yang aktif dan dalam rentang waktu.
     */
    public function scopeActive(Builder $query): Builder
    {
        $now = Carbon::now();
        return $query->where('is_active', true)
                     ->where(function ($q) use ($now) {
                         $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
                     })
                     ->where(function ($q) use ($now) {
                         $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
                     });
    }
}
