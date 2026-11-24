<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShowcaseProject extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = ['user_id', 'title', 'slug', 'summary', 'description', 'url', 'repo_url', 'image', 'stack', 'looking_for_collaborators', 'status', 'is_featured', 'reviewed_by', 'reviewed_at'];

    protected $casts = ['stack' => 'array', 'looking_for_collaborators' => 'boolean', 'is_featured' => 'boolean', 'reviewed_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
