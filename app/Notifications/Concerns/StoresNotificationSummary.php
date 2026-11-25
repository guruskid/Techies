<?php

namespace App\Notifications\Concerns;

use Illuminate\Support\Str;

trait StoresNotificationSummary
{
    /**
     * @return array{title: string}
     */
    public function toArray(object $notifiable): array
    {
        return ['title' => Str::headline(class_basename($this))];
    }
}
