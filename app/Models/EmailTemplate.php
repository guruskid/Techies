<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailTemplate extends Model
{
    protected $fillable = ['name', 'subject', 'body', 'created_by'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Replace merge tags with a recipient's details.
     *
     * @param  array<string, string>  $replacements
     */
    public function renderSubject(array $replacements = []): string
    {
        return $this->replace($this->subject, $replacements);
    }

    /**
     * @param  array<string, string>  $replacements
     */
    public function renderBody(array $replacements = []): string
    {
        return $this->replace($this->body, $replacements);
    }

    /**
     * @param  array<string, string>  $replacements
     */
    private function replace(string $value, array $replacements): string
    {
        $defaults = [
            'site_name' => config('app.name', 'Meet with the Techies'),
            'site_url' => config('app.url'),
        ];

        foreach (array_merge($defaults, $replacements) as $key => $replacement) {
            $value = str_replace(
                ['{{'.$key.'}}', '{{ '.$key.' }}', '{{'.$key.' }}', '{{ '.$key.'}}'],
                (string) $replacement,
                $value,
            );
        }

        return $value;
    }
}
