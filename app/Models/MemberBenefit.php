<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberBenefit extends Model
{
    protected $fillable = [
        'title', 'provider', 'description', 'discount_label', 'category',
        'url', 'is_active', 'sort_order',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
