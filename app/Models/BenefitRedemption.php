<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BenefitRedemption extends Model
{
    protected $fillable = ['member_benefit_id', 'user_id', 'note', 'redeemed_at'];

    protected $casts = ['redeemed_at' => 'datetime'];

    public function benefit()
    {
        return $this->belongsTo(MemberBenefit::class, 'member_benefit_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
