<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConnectedAccount extends Model
{
    protected $fillable = [
        'user_id', 'provider', 'provider_user_id', 'username', 'email', 'name',
        'avatar', 'access_token', 'refresh_token', 'expires_at', 'scopes',
    ];

    protected $hidden = ['access_token', 'refresh_token'];

    protected $casts = [
        'access_token' => 'encrypted',
        'refresh_token' => 'encrypted',
        'expires_at' => 'datetime',
        'scopes' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function label(): string
    {
        return match ($this->provider) {
            'github' => 'GitHub',
            'google' => 'Google / Gmail',
            'facebook' => 'Facebook',
            'figma' => 'Figma',
            default => ucfirst($this->provider),
        };
    }

    public function profileUrl(): ?string
    {
        return match ($this->provider) {
            'github' => $this->username ? 'https://github.com/'.$this->username : null,
            'facebook' => $this->provider_user_id ? 'https://facebook.com/profile.php?id='.$this->provider_user_id : null,
            'figma' => $this->username ? 'https://www.figma.com/@'.$this->username : null,
            default => null,
        };
    }
}
