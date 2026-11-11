<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hub extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'name', 'slug', 'logo', 'cover_image', 'description', 'year_established', 'organization_type', 'email', 'phone', 'website', 'contact_person', 'contact_position', 'country', 'state', 'city', 'address', 'community_size', 'active_members_count', 'programs_offered', 'linkedin', 'facebook', 'instagram', 'x', 'youtube', 'status', 'is_verified', 'is_public'];

    protected $casts = ['is_verified' => 'boolean', 'is_public' => 'boolean'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function technologyAreas()
    {
        return $this->belongsToMany(TechnologyArea::class, 'hub_technology_areas');
    }

    public function teamMembers()
    {
        return $this->hasMany(HubTeamMember::class);
    }

    public function applications()
    {
        return $this->hasMany(HubApplication::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
