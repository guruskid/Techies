<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Every account is a community member, including staff. Give any
     * existing account without a member profile one so it can have a
     * public subdomain profile.
     */
    public function up(): void
    {
        User::query()
            ->doesntHave('profile')
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    $user->ensureProfile();
                }
            });
    }

    public function down(): void
    {
        // Profiles are legitimate member data; nothing to undo.
    }
};
