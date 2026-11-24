<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberBenefit extends Model
{
    protected $fillable = [
        'title', 'provider', 'description', 'discount_label', 'category',
        'url', 'redemption_code', 'redemption_instructions', 'is_active', 'sort_order',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function redemptions()
    {
        return $this->hasMany(BenefitRedemption::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
