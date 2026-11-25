<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Founding status belongs to members who have actually joined, not unpaid or pending accounts.
        DB::table('users')
            ->whereNotIn('membership_status', ['active', 'exempt'])
            ->update(['is_founding_member' => false]);
    }

    public function down(): void
    {
        // No automatic reversal: founding status is historical.
    }
};
