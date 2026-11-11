<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $fillable = ['user_id', 'username', 'first_name', 'last_name', 'phone', 'avatar', 'country', 'state', 'city', 'professional_title', 'technology_category', 'experience_level', 'current_organization', 'education', 'bio', 'linkedin', 'github', 'portfolio', 'website', 'areas_of_interest', 'preferred_participation', 'membership_type', 'is_public', 'profile_completion'];

    protected $casts = ['areas_of_interest' => 'array', 'is_public' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
