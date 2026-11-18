<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->timestamp('waitlisted_at')->nullable()->after('status');
            $table->timestamp('reminded_at')->nullable()->after('waitlisted_at');
        });
    }

    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropColumn(['waitlisted_at', 'reminded_at']);
        });
    }
};
