<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Append-only audit trail of every signing / rejection event. */
class SignatureLog extends Model
{
    protected $guarded = [];
    protected $casts = ['parties' => 'array', 'signed_at' => 'datetime'];

    protected static function booted(): void
    {
        static::updating(fn() => false);   // logs can never be edited
        static::deleting(fn() => false);   // ...or deleted through the application
    }

    public function user() { return $this->belongsTo(User::class); }
    public function travelRequest() { return $this->belongsTo(TravelRequest::class); }
}
