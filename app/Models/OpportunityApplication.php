<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpportunityApplication extends Model
{
    protected $fillable = ['opportunity_id', 'user_id', 'cover_letter', 'status', 'applied_at'];

    /**
     * @return array<int, string>
     */
    public static function statuses(): array
    {
        return ['applied', 'reviewing', 'shortlisted', 'accepted', 'rejected'];
    }

    protected $casts = [
        'applied_at' => 'datetime',
    ];

    public function opportunity()
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
