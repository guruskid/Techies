<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HubTeamMember extends Model
{
    protected $fillable = ['hub_id', 'user_id', 'role'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hub()
    {
        return $this->belongsTo(Hub::class);
    }
}
