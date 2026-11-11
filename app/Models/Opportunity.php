<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Opportunity extends Model
{
    use SoftDeletes;

    protected $fillable = ['title', 'slug', 'description', 'category_id', 'organization', 'hub_id', 'location', 'is_remote', 'application_link', 'deadline', 'eligibility', 'cover_image', 'status', 'submitted_by'];

    protected $casts = ['is_remote' => 'boolean', 'deadline' => 'date'];

    public function category()
    {
        return $this->belongsTo(OpportunityCategory::class, 'category_id');
    }

    public function hub()
    {
        return $this->belongsTo(Hub::class);
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }
}
