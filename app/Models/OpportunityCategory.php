<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpportunityCategory extends Model
{
    protected $fillable = ['name', 'slug'];

    public function opportunities()
    {
        return $this->hasMany(Opportunity::class, 'category_id');
    }
}
