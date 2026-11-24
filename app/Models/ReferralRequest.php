<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralRequest extends Model
{
    protected $fillable = ['referral_offer_id', 'requester_id', 'message', 'status', 'response_note', 'responded_at'];

    protected $casts = ['responded_at' => 'datetime'];

    public function offer()
    {
        return $this->belongsTo(ReferralOffer::class, 'referral_offer_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }
}
