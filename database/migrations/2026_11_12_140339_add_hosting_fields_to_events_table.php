<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('host')->nullable()->after('organizer_id');
            $table->text('key_speakers')->nullable()->after('host');
            $table->text('agenda')->nullable()->after('key_speakers');
            $table->text('requirements')->nullable()->after('agenda');
            $table->string('contact_email')->nullable()->after('requirements');
            $table->string('contact_phone')->nullable()->after('contact_email');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['host', 'key_speakers', 'agenda', 'requirements', 'contact_email', 'contact_phone']);
        });
    }
};
